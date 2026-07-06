<?php

class DatabaseController extends Controller {
    private $databaseModel;

    public function __construct() {
        $this->databaseModel = new DatabaseModel();
    }

    public function index() {
        if ($this->isExpectador()) {
            header('Location: index.php?url=dashboard&status=access_denied');
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = isset($_POST['action']) ? $_POST['action'] : null;

            if ($action === 'backup') {
                $backupFile = $this->databaseModel->createBackup();
                $status = $backupFile ? 'backup_success' : 'backup_error';
                header('Location: index.php?url=database&status=' . $status);
                exit();
            }

            if ($action === 'export_csv') {
                $csvFile = $this->databaseModel->exportCsv();
                $status = $csvFile ? 'export_success' : 'export_error';
                header('Location: index.php?url=database&status=' . $status);
                exit();
            }

            if ($action === 'restore') {
                if (isset($_FILES['sql_file']) && $_FILES['sql_file']['error'] === UPLOAD_ERR_OK) {
                    $uploadName = basename($_FILES['sql_file']['name']);
                    $safeName = preg_replace('/[^a-zA-Z0-9_\-\.]/', '_', $uploadName);
                    $targetName = 'restore-' . date('Ymd-His') . '-' . $safeName;
                    $targetPath = $this->databaseModel->getBackupFolder() . DIRECTORY_SEPARATOR . $targetName;

                    if (move_uploaded_file($_FILES['sql_file']['tmp_name'], $targetPath)) {
                        $restored = $this->databaseModel->restoreBackup($targetPath);
                        $status = $restored ? 'restore_success' : 'restore_error';
                    } else {
                        $status = 'restore_error';
                    }
                } else {
                    $status = 'restore_invalid_file';
                }

                header('Location: index.php?url=database&status=' . $status);
                exit();
            }
        }

        if (isset($_GET['action']) && $_GET['action'] === 'download' && isset($_GET['file'])) {
            $this->download($_GET['file']);
            return;
        }

        if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['file'])) {
            $fileName = basename($_GET['file']);
            $deleted = $this->databaseModel->deleteBackup($fileName);
            $status = $deleted ? 'delete_success' : 'delete_error';
            header('Location: index.php?url=database&status=' . $status);
            exit();
        }

        $backups = $this->databaseModel->listBackups();

        $data = [
            'page_title' => 'Base de Datos - Starlink Control',
            'backups' => $backups
        ];

        $this->render('modules/database', $data);
    }

    private function download($fileName) {
        $safeName = basename($fileName);
        if (!$this->databaseModel->isValidBackupFile($safeName)) {
            header('HTTP/1.0 404 Not Found');
            echo '<h1>Archivo no encontrado</h1>';
            exit();
        }

        $filePath = $this->databaseModel->getBackupFolder() . DIRECTORY_SEPARATOR . $safeName;
        if (!is_readable($filePath)) {
            header('HTTP/1.0 404 Not Found');
            echo '<h1>Archivo no encontrado</h1>';
            exit();
        }

        header('Content-Description: File Transfer');
        header('Content-Type: application/sql');
        header('Content-Disposition: attachment; filename="' . $safeName . '"');
        header('Content-Length: ' . filesize($filePath));
        readfile($filePath);
        exit();
    }
}
