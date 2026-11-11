<?php
/**
 * Configuración de Inteligencia Artificial para Jeremy (Híbrido)
 * 
 * Si 'gemini_api_key' está configurado, Jeremy utiliza el modelo Gemini Flash de Google
 * para responder preguntas complejas en lenguaje natural.
 * 
 * Si está vacío, Jeremy opera automáticamente en Modo Local Determinista ($0, offline y ultrarrápido).
 */

$defaultConfig = [
    'gemini_api_key' => getenv('GEMINI_API_KEY') ?: '',
    'model' => 'gemini-2.5-flash',
    'temperature' => 0.4,
    'max_tokens' => 800
];

$localConfigPath = __DIR__ . '/ai.local.php';
if (file_exists($localConfigPath)) {
    $localConfig = require $localConfigPath;
    if (is_array($localConfig)) {
        $defaultConfig = array_merge($defaultConfig, $localConfig);
    }
}

return $defaultConfig;

