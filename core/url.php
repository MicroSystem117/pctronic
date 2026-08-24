<?php

function app_url($path = '') {
    $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (isset($_SERVER['SERVER_PORT']) && (int) $_SERVER['SERVER_PORT'] === 443);
    $scheme = $isHttps ? 'https' : 'http';
    $host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost';
    $basePath = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/'));
    $basePath = rtrim($basePath, '/.');

    return $scheme . '://' . $host . ($basePath === '' ? '' : $basePath)
        . '/' . ltrim($path, '/');
}