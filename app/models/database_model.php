<?php

class DatabaseModel {
    private $backupDir;

    public function __construct() {
        $this->backupDir = realpath(__DIR__ . '/../../storage/backups');
        if (!$this->backupDir) {
            $this->backupDir = __DIR__ . '/../../storage/backups';
            if (!is_dir($this->backupDir)) {
                mkdir($this->backupDir, 0755, true);
            }
        }
    }

    public function getBackupFolder() {
        return $this->backupDir;
    }

    public function listBackups() {
        $backups = [];
        $files = glob($this->backupDir . '/*.{sql,zip,csv}', GLOB_BRACE);

        if ($files !== false) {
            foreach ($files as $filePath) {
                $backups[] = [
                    'name' => basename($filePath),
                    'path' => $filePath,
                    'size' => filesize($filePath),
                    'modified' => filemtime($filePath),
                    'type' => pathinfo($filePath, PATHINFO_EXTENSION)
                ];
            }
        }

        usort($backups, function ($a, $b) {
            return $b['modified'] <=> $a['modified'];
        });

        return $backups;
    }

    public function createBackup() {
        $db = Database::connect();

        $sql = "-- Respaldo de Starlink Control\n";
        $sql .= "SET FOREIGN_KEY_CHECKS=0;\n\n";

        $tables = $db->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);

        foreach ($tables as $table) {
            $createResult = $db->query("SHOW CREATE TABLE `{$table}`")->fetch(PDO::FETCH_ASSOC);
            if (empty($createResult['Create Table'])) {
                continue;
            }

            $sql .= "DROP TABLE IF EXISTS `{$table}`;\n";
            $sql .= $createResult['Create Table'] . ";\n\n";

            $rows = $db->query("SELECT * FROM `{$table}`")->fetchAll(PDO::FETCH_ASSOC);
            if (!empty($rows)) {
                $columns = array_map(function ($col) {
                    return "`{$col}`";
                }, array_keys($rows[0]));
                $columnsList = implode(', ', $columns);

                foreach ($rows as $row) {
                    $values = array_map([$this, 'escapeValue'], array_values($row));
                    $sql .= "INSERT INTO `{$table}` ({$columnsList}) VALUES (" . implode(', ', $values) . ");\n";
                }
                $sql .= "\n";
            }
        }

        $sql .= "SET FOREIGN_KEY_CHECKS=1;\n";

        $fileName = 'backup-' . date('Ymd-His') . '.sql';
        $filePath = $this->backupDir . DIRECTORY_SEPARATOR . $fileName;

        if (file_put_contents($filePath, $sql) !== false) {
            return $fileName;
        }

        return false;
    }

    public function exportCsv() {
        if (!class_exists('ZipArchive')) {
            return $this->exportCsvFallback();
        }

        $db = Database::connect();
        $tables = $db->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
        $tmpDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'starlink_csv_export_' . uniqid();
        mkdir($tmpDir, 0755, true);

        foreach ($tables as $table) {
            $rows = $db->query("SELECT * FROM `{$table}`")->fetchAll(PDO::FETCH_ASSOC);
            $csvPath = $tmpDir . DIRECTORY_SEPARATOR . $table . '.csv';
            $fp = fopen($csvPath, 'w');
            if ($fp === false) {
                continue;
            }

            if (!empty($rows)) {
                fputcsv($fp, array_keys($rows[0]), ',', '"', '\\');
                foreach ($rows as $row) {
                    fputcsv($fp, array_values($row), ',', '"', '\\');
                }
            } else {
                $columns = $db->query("SHOW COLUMNS FROM `{$table}`")->fetchAll(PDO::FETCH_COLUMN);
                if (!empty($columns)) {
                    fputcsv($fp, $columns, ',', '"', '\\');
                }
            }

            fclose($fp);
        }

        $zipName = 'export-csv-' . date('Ymd-His') . '.zip';
        $zipPath = $this->backupDir . DIRECTORY_SEPARATOR . $zipName;

        $zip = new ZipArchive();
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            $this->deleteDirectory($tmpDir);
            return false;
        }

        $files = glob($tmpDir . DIRECTORY_SEPARATOR . '*.csv');
        foreach ($files as $file) {
            $zip->addFile($file, basename($file));
        }
        $zip->close();
        $this->deleteDirectory($tmpDir);

        return is_file($zipPath) ? $zipName : false;
    }

    private function exportCsvFallback() {
        $db = Database::connect();
        $tables = $db->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
        $fileName = 'export-csv-' . date('Ymd-His') . '.csv';
        $filePath = $this->backupDir . DIRECTORY_SEPARATOR . $fileName;
        $fp = fopen($filePath, 'w');

        if ($fp === false) {
            return false;
        }

        foreach ($tables as $table) {
            $rows = $db->query("SELECT * FROM `{$table}`")->fetchAll(PDO::FETCH_ASSOC);
            fputcsv($fp, ['Tabla', $table], ',', '"', '\\');

            if (!empty($rows)) {
                fputcsv($fp, array_keys($rows[0]), ',', '"', '\\');
                foreach ($rows as $row) {
                    fputcsv($fp, array_values($row), ',', '"', '\\');
                }
            } else {
                $columns = $db->query("SHOW COLUMNS FROM `{$table}`")->fetchAll(PDO::FETCH_COLUMN);
                if (!empty($columns)) {
                    fputcsv($fp, $columns, ',', '"', '\\');
                }
            }

            fputcsv($fp, [], ',', '"', '\\');
        }

        fclose($fp);
        return is_file($filePath) ? $fileName : false;
    }

    private function deleteDirectory($dir) {
        if (!is_dir($dir)) {
            return;
        }

        $items = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($items as $item) {
            if ($item->isDir()) {
                rmdir($item->getPathname());
            } else {
                unlink($item->getPathname());
            }
        }

        rmdir($dir);
    }

    public function restoreBackup($backupFilePath) {
        if (!file_exists($backupFilePath)) {
            return false;
        }

        $sql = file_get_contents($backupFilePath);
        if ($sql === false) {
            return false;
        }

        $db = Database::connect();
        try {
            $db->exec('SET FOREIGN_KEY_CHECKS=0;');
            $db->exec($sql);
            $db->exec('SET FOREIGN_KEY_CHECKS=1;');
            return true;
        } catch (PDOException $exception) {
            return false;
        }
    }

    public function deleteBackup($fileName) {
        $safeName = basename($fileName);
        $filePath = $this->backupDir . DIRECTORY_SEPARATOR . $safeName;

        if (!$this->isValidBackupFile($safeName)) {
            return false;
        }

        return unlink($filePath);
    }

    public function isValidBackupFile($fileName) {
        $safeName = basename($fileName);
        $filePath = $this->backupDir . DIRECTORY_SEPARATOR . $safeName;
        $extension = strtolower(pathinfo($safeName, PATHINFO_EXTENSION));
        return is_file($filePath) && in_array($extension, ['sql', 'zip', 'csv'], true);
    }

    private function escapeValue($value) {
        if ($value === null) {
            return 'NULL';
        }

        $escaped = str_replace(
            ["\\", "\0", "\n", "\r", "\x1a", "'"],
            ["\\\\", "\\0", "\\n", "\\r", "\\Z", "\\'"],
            $value
        );

        return "'{$escaped}'";
    }
}
