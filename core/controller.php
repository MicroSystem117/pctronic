<?php

class Controller {
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