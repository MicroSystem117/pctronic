<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once "../config/database.php";
require_once "../core/controller.php";
require_once "../core/router.php";
require_once "../core/url.php";

spl_autoload_register(function ($className) {
    // Si la clase es 'ClientModel', la convierte a 'client_model'
    $formattedName = strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $className));
    $modelPath = "../app/models/" . $formattedName . ".php";
    
    if (file_exists($modelPath)) {
        require_once $modelPath;
    }
});

$app = new Router();
$app->run();