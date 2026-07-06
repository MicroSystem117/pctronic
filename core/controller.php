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
}   