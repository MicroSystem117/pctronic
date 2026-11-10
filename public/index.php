<?php
// Cabeceras de seguridad HTTP
header('X-Frame-Options: SAMEORIGIN');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: strict-origin-when-cross-origin');

if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.use_strict_mode', '1');
    session_set_cookie_params([
        'httponly' => true,
        'samesite' => 'Lax',
        'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'
    ]);
    session_start();
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

require_once "../config/database.php";
require_once "../core/controller.php";
require_once "../core/totp.php";
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