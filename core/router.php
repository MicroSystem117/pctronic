<?php

class Router {
    public function run() {
        $url = isset($_GET['url']) ? rtrim($_GET['url'], '/') : 'dashboard';
        $url = filter_var($url, FILTER_SANITIZE_URL);
        
        $urlParams = explode('/', $url);

        // Capturamos el nombre base desde la URL
        $rawName = !empty($urlParams[0]) ? strtolower($urlParams[0]) : 'dashboard';

        $publicRoutes = ['login', 'register', 'logout', 'auth'];
        if (!isset($_SESSION['user_id']) && !in_array($rawName, $publicRoutes, true)) {
            header('Location: index.php?url=login');
            exit();
        }

        // CORRECCIÓN TÁCTICA: Mapeamos las rutas de acceso y los plurales a los controladores físicos
        $controllerName = $rawName;
        if ($rawName === 'login' || $rawName === 'register' || $rawName === 'logout') {
            $controllerName = 'auth';
        } elseif ($rawName === 'clients') {
            $controllerName = 'clients';
        } elseif ($rawName === 'antenas') {
            $controllerName = 'antena';
        } elseif ($rawName === 'plans') {
            $controllerName = 'plan';
        } elseif ($rawName === 'countries') {
            $controllerName = 'country';
        } elseif ($rawName === 'users') {
            $controllerName = 'users';
        }
        
        // Formato para la Clase (ej: ClientController o AntenaController)
        $controllerClass = ucfirst($controllerName) . 'Controller';
        
        // Formato para el Archivo Físico (ej: ../app/controllers/client_controller.php)
        $controllerFile = "../app/controllers/" . $controllerName . "_controller.php";

        $methodName = (isset($urlParams[1]) && !empty($urlParams[1])) ? $urlParams[1] : 'index';
        if (in_array($rawName, ['login', 'register', 'logout'], true)) {
            $methodName = $rawName;
            $params = [];
        } else {
            $params = array_slice($urlParams, 2);
        }

        if (file_exists($controllerFile)) {
            require_once $controllerFile;

            if (class_exists($controllerClass)) {
                $controllerObject = new $controllerClass();

                if (method_exists($controllerObject, $methodName)) {
                    call_user_func_array([$controllerObject, $methodName], $params);
                } else {
                    $this->send404("El método '{$methodName}' no fue encontrado.");
                }
            } else {
                $this->send404("La clase '{$controllerClass}' no existe.");
            }
        } else {
            $this->send404("El archivo del controlador '{$controllerFile}' no existe.");
        }
    }

    private function send404($errorDetail = "") {
        header("HTTP/1.0 404 Not Found");
        echo "<h1>Error 404 - Página no encontrada</h1>";
        echo "<p>{$errorDetail}</p>";
        exit();
    }
}