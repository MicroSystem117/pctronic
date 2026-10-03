<?php

class LogModel {
    private $db;

    public function __construct() {
        $this->db = Database::connect();
        $this->ensureSchema();
    }

    /**
     * Asegura que la tabla activity_logs exista en la base de datos de manera automática.
     */
    public function ensureSchema() {
        static $checked = false;
        if ($checked) return;
        $checked = true;

        try {
            $this->db->exec("CREATE TABLE IF NOT EXISTS `activity_logs` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `user_id` INT NULL,
                `user_name` VARCHAR(100) NOT NULL,
                `user_role` VARCHAR(50) NOT NULL,
                `action` VARCHAR(50) NOT NULL,
                `module` VARCHAR(50) NOT NULL,
                `description` TEXT NOT NULL,
                `ip_address` VARCHAR(45) NULL,
                `user_agent` VARCHAR(255) NULL,
                `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                INDEX `idx_user` (`user_id`),
                INDEX `idx_module` (`module`),
                INDEX `idx_action` (`action`),
                INDEX `idx_created_at` (`created_at`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
        } catch (Throwable $e) {
            error_log("LogModel::ensureSchema error: " . $e->getMessage());
        }
    }

    /**
     * Registra una acción de usuario en el sistema de manera global y segura.
     *
     * @param string $action Tipo de acción (LOGIN, LOGOUT, CREAR, EDITAR, ELIMINAR, APROBAR, RECHAZAR, EXONERAR, etc.)
     * @param string $module Módulo donde ocurrió (Autenticación, Antenas, Pagos, Clientes, etc.)
     * @param string $description Detalle descriptivo de la acción
     * @param int|null $userId ID del usuario (si es null, lo obtiene de la sesión)
     * @param string|null $userName Nombre del usuario
     * @param string|null $userRole Rol del usuario
     * @return bool
     */
    public static function log($action, $module, $description, $userId = null, $userName = null, $userRole = null) {
        try {
            $db = Database::connect();
            if (!$db) return false;

            // Instancia para verificar tabla
            $instance = new self();

            if ($userId === null && isset($_SESSION['user_id'])) {
                $userId = intval($_SESSION['user_id']);
            }
            if ($userName === null) {
                $userName = $_SESSION['user'] ?? $_SESSION['user_name'] ?? 'Sistema';
            }
            if ($userRole === null) {
                $userRole = $_SESSION['user_role'] ?? 'Sistema';
            }

            // Obtener IP del cliente
            $ip = '127.0.0.1';
            if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
                $ip = $_SERVER['HTTP_CLIENT_IP'];
            } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                $parts = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
                $ip = trim($parts[0]);
            } elseif (!empty($_SERVER['REMOTE_ADDR'])) {
                $ip = $_SERVER['REMOTE_ADDR'];
            }

            $userAgent = !empty($_SERVER['HTTP_USER_AGENT']) ? substr($_SERVER['HTTP_USER_AGENT'], 0, 255) : null;

            $stmt = $db->prepare("INSERT INTO `activity_logs` 
                (`user_id`, `user_name`, `user_role`, `action`, `module`, `description`, `ip_address`, `user_agent`, `created_at`) 
                VALUES (:user_id, :user_name, :user_role, :action, :module, :description, :ip, :ua, NOW())");

            return $stmt->execute([
                ':user_id'     => $userId,
                ':user_name'   => $userName,
                ':user_role'   => $userRole,
                ':action'      => strtoupper(trim($action)),
                ':module'      => trim($module),
                ':description' => trim($description),
                ':ip'          => substr($ip, 0, 45),
                ':ua'          => $userAgent
            ]);
        } catch (Throwable $e) {
            error_log("LogModel::log failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtiene registros filtrados para la vista de auditoría.
     */
    public function getLogs($filters = [], $limit = 500) {
        $sql = "SELECT * FROM `activity_logs` WHERE 1=1";
        $params = [];

        if (!empty($filters['from'])) {
            $sql .= " AND created_at >= :from";
            $params[':from'] = $filters['from'] . ' 00:00:00';
        }

        if (!empty($filters['to'])) {
            $sql .= " AND created_at <= :to";
            $params[':to'] = $filters['to'] . ' 23:59:59';
        }

        if (!empty($filters['module']) && $filters['module'] !== 'all') {
            $sql .= " AND module = :module";
            $params[':module'] = $filters['module'];
        }

        if (!empty($filters['action']) && $filters['action'] !== 'all') {
            $sql .= " AND action = :action";
            $params[':action'] = $filters['action'];
        }

        if (!empty($filters['user_id'])) {
            $sql .= " AND user_id = :user_id";
            $params[':user_id'] = intval($filters['user_id']);
        }

        if (!empty($filters['search'])) {
            $sql .= " AND (description LIKE :search OR user_name LIKE :search OR ip_address LIKE :search)";
            $params[':search'] = '%' . trim($filters['search']) . '%';
        }

        $sql .= " ORDER BY id DESC LIMIT " . intval($limit);

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Throwable $e) {
            error_log("LogModel::getLogs error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtiene estadísticas de actividad para las tarjetas superiores.
     */
    public function getStats() {
        $stats = [
            'total' => 0,
            'today' => 0,
            'users_today' => 0,
            'top_module' => 'N/A'
        ];

        try {
            // Total
            $stats['total'] = (int) $this->db->query("SELECT COUNT(*) FROM `activity_logs`")->fetchColumn();

            // Hoy
            $stmt = $this->db->query("SELECT COUNT(*) FROM `activity_logs` WHERE DATE(created_at) = CURDATE()");
            $stats['today'] = (int) $stmt->fetchColumn();

            // Usuarios activos hoy
            $stmt = $this->db->query("SELECT COUNT(DISTINCT user_name) FROM `activity_logs` WHERE DATE(created_at) = CURDATE()");
            $stats['users_today'] = (int) $stmt->fetchColumn();

            // Módulo más activo hoy
            $stmt = $this->db->query("SELECT module, COUNT(*) as cnt FROM `activity_logs` WHERE DATE(created_at) = CURDATE() GROUP BY module ORDER BY cnt DESC LIMIT 1");
            $top = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($top && !empty($top['module'])) {
                $stats['top_module'] = $top['module'] . ' (' . $top['cnt'] . ')';
            }
        } catch (Throwable $e) {
            error_log("LogModel::getStats error: " . $e->getMessage());
        }

        return $stats;
    }

    /**
     * Obtiene la lista única de módulos y acciones para los selectores de filtro.
     */
    public function getFilterOptions() {
        $modules = [];
        $actions = [];
        try {
            $modules = $this->db->query("SELECT DISTINCT module FROM `activity_logs` ORDER BY module ASC")->fetchAll(PDO::FETCH_COLUMN);
            $actions = $this->db->query("SELECT DISTINCT action FROM `activity_logs` ORDER BY action ASC")->fetchAll(PDO::FETCH_COLUMN);
        } catch (Throwable $e) {
            error_log("LogModel::getFilterOptions error: " . $e->getMessage());
        }
        return [
            'modules' => $modules,
            'actions' => $actions
        ];
    }

    /**
     * Elimina registros anteriores a X días (mantenimiento para no saturar BD).
     */
    public function clearOld($days = 90) {
        try {
            $stmt = $this->db->prepare("DELETE FROM `activity_logs` WHERE created_at < DATE_SUB(NOW(), INTERVAL :days DAY)");
            return $stmt->execute([':days' => intval($days)]);
        } catch (Throwable $e) {
            error_log("LogModel::clearOld error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Vacía completamente la tabla de registros (solo Administrador).
     */
    public function clearAll() {
        try {
            return (bool) $this->db->exec("TRUNCATE TABLE `activity_logs`");
        } catch (Throwable $e) {
            error_log("LogModel::clearAll error: " . $e->getMessage());
            return false;
        }
    }
}
