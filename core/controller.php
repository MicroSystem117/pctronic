<?php

class Controller {
    public static function getCsrfToken() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    public static function getCsrfInput() {
        return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(self::getCsrfToken(), ENT_QUOTES, 'UTF-8') . '">';
    }

    protected function validateCsrfToken() {
        $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;
        $sessionToken = $_SESSION['csrf_token'] ?? '';

        if (empty($token) || empty($sessionToken) || !hash_equals($sessionToken, (string)$token)) {
            $isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
            if ($isAjax) {
                header('Content-Type: application/json; charset=utf-8');
                http_response_code(403);
                echo json_encode([
                    'success' => false,
                    'message' => 'Token de seguridad inválido o expirado. Por favor recarga la página.'
                ]);
                exit();
            }
            http_response_code(403);
            die('Error 403: Solicitud rechazada por validación de seguridad (CSRF inválido o expirado). Por favor recarga la página.');
        }
        return true;
    }

    public function render($view, $data = []) {
        if (!empty($data)) {
            extract($data);
        }

        // CORRECCIÓN: Añadimos ../ para que busque correctamente las vistas
        $viewPath = "../app/views/" . $view . ".php";

        if (file_exists($viewPath)) {
            $content = $viewPath;
            require_once "../app/views/template.php";
        } else {
            die("La vista '{$view}' no existe.");
        }
    }

    protected function getUserRole() {
        if (empty($_SESSION['user_role']) && !empty($_SESSION['user_id'])) {
            try {
                $db = Database::connect();
                $stmt = $db->prepare('SELECT l.user_role FROM `user` u LEFT JOIN `level` l ON u.id_level = l.id_level WHERE u.id_user = :id');
                $stmt->execute([':id' => intval($_SESSION['user_id'])]);
                $role = $stmt->fetchColumn();
                if ($role) {
                    $_SESSION['user_role'] = $role;
                }
            } catch (Throwable $e) {
                // Silenciosamente ignorar fallo de conexión
            }
        }
        return isset($_SESSION['user_role']) ? $_SESSION['user_role'] : null;
    }

    protected function getUserCi() {
        return isset($_SESSION['user_ci']) ? $_SESSION['user_ci'] : null;
    }

    protected function isAdmin() {
        return $this->getUserRole() === 'Administrador';
    }

    protected function isModerator() {
        return $this->getUserRole() === 'Moderador';
    }

    protected function isExpectador() {
        return $this->getUserRole() === 'Expectador';
    }

    protected function requireAdmin() {
        if (!$this->isAdmin()) {
            $isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
            if ($isAjax) {
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(['success' => false, 'message' => 'Acceso denegado. Se requiere nivel de Administrador.']);
                exit();
            }
            header('Location: index.php?url=dashboard&status=access_denied');
            exit();
        }
    }

    public function validateActiveSession() {
        if (!isset($_SESSION['user_id'])) {
            return true;
        }

        $db = Database::connect();
        $stmt = $db->prepare('SELECT id_session FROM user_sessions WHERE user_id = :user_id AND session_id = :session_id AND active = 1 LIMIT 1');
        $stmt->execute([':user_id' => $_SESSION['user_id'], ':session_id' => session_id()]);
        if (!$stmt->fetch(PDO::FETCH_ASSOC)) {
            session_unset();
            $isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
            if ($isAjax) {
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(['success' => false, 'message' => 'Tu sesión ha sido revocada o ha expirado. Por favor inicia sesión nuevamente.']);
                exit();
            }
            header('Location: index.php?url=login&status=session_revoked');
            exit();
        }

        // Sincronizar rol y datos del usuario en sesión si faltan
        if (empty($_SESSION['user_role']) || empty($_SESSION['user'])) {
            try {
                $userStmt = $db->prepare('SELECT u.ci, u.name, u.surname, l.user_role FROM `user` u LEFT JOIN `level` l ON u.id_level = l.id_level WHERE u.id_user = :id');
                $userStmt->execute([':id' => intval($_SESSION['user_id'])]);
                $userData = $userStmt->fetch(PDO::FETCH_ASSOC);
                if ($userData) {
                    $_SESSION['user_role'] = $userData['user_role'] ?? 'Expectador';
                    if (empty($_SESSION['user_ci'])) $_SESSION['user_ci'] = $userData['ci'];
                    if (empty($_SESSION['user'])) $_SESSION['user'] = trim(($userData['name'] ?? '') . ' ' . ($userData['surname'] ?? ''));
                }
            } catch (Throwable $e) {}
        }

        $stmt = $db->prepare('UPDATE user_sessions SET last_seen = CURRENT_TIMESTAMP WHERE user_id = :user_id AND session_id = :session_id');
        $stmt->execute([':user_id' => $_SESSION['user_id'], ':session_id' => session_id()]);
        return true;
    }
}   