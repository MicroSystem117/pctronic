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

        $stmt = $db->prepare('UPDATE user_sessions SET last_seen = CURRENT_TIMESTAMP WHERE user_id = :user_id AND session_id = :session_id');
        $stmt->execute([':user_id' => $_SESSION['user_id'], ':session_id' => session_id()]);
        return true;
    }
}   