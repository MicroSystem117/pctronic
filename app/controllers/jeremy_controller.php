<?php

class JeremyController extends Controller {

    public function index() {
        // Redirige al manual si se accede por GET
        header('Location: index.php?url=manual');
        exit();
    }

    /**
     * Endpoint API para procesar preguntas u órdenes hacia Jeremy
     * POST index.php?url=jeremy/ask
     */
    public function ask() {
        header('Content-Type: application/json; charset=utf-8');

        // 1. Verificación de Autenticación
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (empty($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode([
                'success' => false,
                'message' => 'Sesión requerida para interactuar con Jeremy.'
            ]);
            exit();
        }

        // 2. Validación de Seguridad CSRF
        $token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? $_POST['csrf_token'] ?? null;
        $sessionToken = $_SESSION['csrf_token'] ?? '';
        if (empty($token) || empty($sessionToken) || !hash_equals($sessionToken, (string)$token)) {
            http_response_code(403);
            echo json_encode([
                'success' => false,
                'message' => 'Token de seguridad inválido o expirado.'
            ]);
            exit();
        }

        // 3. Obtener Prompt y Ruta Actual
        $rawInput = file_get_contents('php://input');
        $json = json_decode($rawInput, true);

        $prompt = trim($json['prompt'] ?? $_POST['prompt'] ?? '');
        $currentUrl = trim($json['current_url'] ?? $_POST['current_url'] ?? 'dashboard');

        if (empty($prompt)) {
            echo json_encode([
                'success' => false,
                'message' => 'Por favor escribe una consulta o comando.'
            ]);
            exit();
        }

        // 4. Cargar Configuración de IA
        $aiConfig = require __DIR__ . '/../../config/ai.php';
        $apiKey = $aiConfig['gemini_api_key'] ?? '';

        // Si hay API Key de Gemini configurada -> Modo IA Cloud
        if (!empty($apiKey)) {
            $aiResponse = $this->queryGemini($prompt, $currentUrl, $aiConfig);
            if ($aiResponse['success']) {
                echo json_encode($aiResponse);
                exit();
            }
            // Si la API falla, hacemos fallback transparente al motor local
        }

        // 5. Modo Local Determinista / NLP Inteligente ($0, Offline)
        $localResponse = $this->processLocalIntent($prompt, $currentUrl);
        echo json_encode($localResponse);
        exit();
    }

    /**
     * Motor Local Inteligente (NLP de Intenciones y Acciones)
     */
    private function processLocalIntent($prompt, $currentUrl) {
        $normalized = mb_strtolower($prompt, 'UTF-8');
        // Quitar acentos para matching robusto
        $unaccented = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $normalized);

        $action = null;
        $actionLabel = null;
        $actionUrl = null;
        $reply = '';

        // Intención: Respaldo de Base de Datos
        if (preg_match('/\b(backup|respaldo|sql|base de datos|guardar bd|copia de seguridad)\b/i', $unaccented)) {
            $userRole = $_SESSION['user_role'] ?? '';
            if ($userRole !== 'Administrador') {
                $reply = 'La generación de respaldos SQL está reservada únicamente para administradores del sistema.';
            } else {
                $reply = '¡Entendido! He preparado la copia de seguridad de la base de datos de Starlink Control. Tu descarga iniciará de inmediato.';
                $action = 'backup';
                $actionLabel = 'Descargar Respaldo SQL';
                $actionUrl = 'index.php?url=database&action=export';
            }
        }
        // Intención: Exportar a Excel / CSV
        elseif (preg_match('/\b(excel|csv|exportar|descargar tabla|reporte excel|hoja de calculo)\b/i', $unaccented)) {
            $reply = 'Listo, generando el reporte en formato Excel/CSV con los registros de la tabla actual.';
            $action = 'export_excel';
            $actionLabel = 'Exportar a Excel';
        }
        // Intención: Filtrar Antenas Suspendidas (Corte Técnico)
        elseif (preg_match('/\b(corte|corte tecnico|suspendid|inactiv|cortad|antenas en corte)\b/i', $unaccented)) {
            $reply = 'Filtrando en la tabla de antenas aquellas que tienen servicio suspendido (corte técnico preventivo).';
            $action = 'filter_suspended';
            $actionLabel = 'Ver Antenas Suspendidas';
            $actionUrl = 'index.php?url=antenas';
        }
        // Intención: Filtrar Antenas Morosas / Vencidas
        elseif (preg_match('/\b(moros|vencid|deud|por cobrar|deuda|saldo pendiente|quien debe)\b/i', $unaccented)) {
            $reply = 'Localizando terminales con mensualidades vencidas para gestión prioritaria de cobranza.';
            $action = 'filter_overdue';
            $actionLabel = 'Ver Antenas Morosas';
            $actionUrl = 'index.php?url=antenas';
        }
        // Intención: Registrar Nuevo Cliente
        elseif (preg_match('/\b(nuevo cliente|crear cliente|registrar cliente|agregar cliente|anadir cliente)\b/i', $unaccented)) {
            $reply = 'Abriendo el formulario de registro para incorporar un nuevo cliente al directorio.';
            $action = 'new_client';
            $actionLabel = 'Registrar Nuevo Cliente';
            $actionUrl = 'index.php?url=clients';
        }
        // Intención: Cobro Rápido / Registrar Pago
        elseif (preg_match('/\b(cobrar|pago|registrar pago|nuevo pago|abono|comprobante|cobranza|pagar)\b/i', $unaccented)) {
            $reply = 'Abriendo el módulo de cobranza para registrar un pago individual o múltiple con su comprobante.';
            $action = 'new_payment';
            $actionLabel = 'Registrar Pago';
            $actionUrl = 'index.php?url=payments';
        }
        // Intención: Tours y Aprendizaje
        elseif (preg_match('/\b(tour|guia|como funciona|paso a paso|tutorial|ensenarme|aprender)\b/i', $unaccented)) {
            $reply = 'Iniciando recorrido interactivo guiado en pantalla para enseñarte los procedimientos oficiales.';
            $action = 'start_tour';
            $actionLabel = 'Iniciar Tour';
        }
        // Intención: Manual de Usuario
        elseif (preg_match('/\b(manual|documentacion|instrucciones|guia completa|capitulos)\b/i', $unaccented)) {
            $reply = 'Puedes consultar o imprimir la documentación completa en la biblioteca oficial:';
            $action = 'open_manual';
            $actionLabel = 'Abrir Manual (10 Capítulos)';
            $actionUrl = 'index.php?url=manual';
        }
        // Intención: WhatsApp
        elseif (preg_match('/\b(whatsapp|recordar|mensaje|notificar cliente)\b/i', $unaccented)) {
            $reply = 'Para enviar un cobro por WhatsApp, ubica al cliente en la tabla de Antenas y haz clic en el botón verde con el ícono de WhatsApp.';
            $action = 'nav_antenas';
            $actionLabel = 'Ir a Antenas';
            $actionUrl = 'index.php?url=antenas';
        }
        // Intención: Exoneración de deuda
        elseif (preg_match('/\b(exoner|perdonar deuda|deuda previa|arreglar saldo)\b/i', $unaccented)) {
            $reply = 'La exoneración de deuda previa se realiza desde el módulo de Antenas presionando el botón "Exonerar". Establece el saldo en $0.00 y recalcula desde el mes actual.';
            $action = 'nav_antenas';
            $actionLabel = 'Ir a Antenas';
            $actionUrl = 'index.php?url=antenas';
        }
        // Intención: 2FA / Seguridad
        elseif (preg_match('/\b(2fa|totp|google authenticator|clave|seguridad|token)\b/i', $unaccented)) {
            $reply = 'Puedes activar la autenticación de dos factores (2FA / TOTP) y configurar tus preguntas de seguridad en tu panel de usuario.';
            $action = 'nav_security';
            $actionLabel = 'Gestionar Seguridad';
            $actionUrl = 'index.php?url=auth/manage_security';
        }
        // Búsqueda en el Manifiesto / Respuesta genérica amigable
        else {
            $reply = "Hola, soy **Jeremy**. Puedo ejecutar acciones directas en la app (como descargar respaldos SQL, exportar Excel, filtrar morosos o abrir cobranzas) o guiarte paso a paso con los tours del manual. ¿En qué te puedo colaborar hoy?";
        }

        return [
            'success' => true,
            'mode' => 'local',
            'reply' => $reply,
            'action' => $action,
            'actionLabel' => $actionLabel,
            'actionUrl' => $actionUrl
        ];
    }

    /**
     * Conexión con Google Gemini API (Modo IA Generativa)
     */
    private function queryGemini($prompt, $currentUrl, $config) {
        $apiKey = $config['gemini_api_key'];
        $model = $config['model'] ?? 'gemini-2.5-flash';
        $endpoint = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key=" . urlencode($apiKey);

        // Cargar extracto del manual de usuario para grounding inmutable
        $manualPath = __DIR__ . '/../../MANUAL_DE_USUARIO.md';
        $manualContext = file_exists($manualPath) ? substr(file_get_contents($manualPath), 0, 12000) : '';

        $systemInstruction = "Eres Jeremy, el copiloto oficial y asistente operativo de la plataforma Starlink Control Manager. "
            . "Tu misión es responder con máxima claridad, precisión y cortesía profesional. "
            . "Te estás ejecutando actualmente en la pantalla: {$currentUrl}. "
            . "Basa tus respuestas estrictamente en este manual de operaciones: \n\n" . $manualContext . "\n\n"
            . "Si el usuario solicita una acción ejecutable como respaldar, exportar a excel, filtrar morosos o registrar pago, añade al final de tu respuesta una etiqueta de acción exacta: "
            . "[ACTION:backup], [ACTION:export_excel], [ACTION:filter_overdue], [ACTION:filter_suspended], [ACTION:new_payment], [ACTION:new_client] o [ACTION:manual].";

        $payload = [
            'contents' => [
                [
                    'role' => 'user',
                    'parts' => [
                        ['text' => $systemInstruction . "\n\nPregunta del usuario: " . $prompt]
                    ]
                ]
            ],
            'generationConfig' => [
                'temperature' => $config['temperature'] ?? 0.4,
                'maxOutputTokens' => $config['max_tokens'] ?? 800
            ]
        ];

        $ch = curl_init($endpoint);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 8,
            CURLOPT_SSL_VERIFYPEER => true
        ]);

        $rawResponse = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($httpCode !== 200 || empty($rawResponse)) {
            // Falla en API -> retorna fallback local
            return ['success' => false, 'error' => $curlError ?: "HTTP $httpCode"];
        }

        $result = json_decode($rawResponse, true);
        $replyText = $result['candidates'][0]['content']['parts'][0]['text'] ?? '';

        if (empty($replyText)) {
            return ['success' => false, 'error' => 'Respuesta vacía de IA'];
        }

        // Extraer etiquetas de acción si existen
        $action = null;
        $actionLabel = null;
        $actionUrl = null;

        if (preg_match('/\[ACTION:(.*?)\]/', $replyText, $matches)) {
            $actKey = trim($matches[1]);
            $replyText = trim(str_replace($matches[0], '', $replyText));

            switch ($actKey) {
                case 'backup':
                    $action = 'backup';
                    $actionLabel = 'Descargar Respaldo SQL';
                    $actionUrl = 'index.php?url=database&action=export';
                    break;
                case 'export_excel':
                    $action = 'export_excel';
                    $actionLabel = 'Exportar a Excel';
                    break;
                case 'filter_overdue':
                    $action = 'filter_overdue';
                    $actionLabel = 'Ver Antenas Morosas';
                    $actionUrl = 'index.php?url=antenas';
                    break;
                case 'filter_suspended':
                    $action = 'filter_suspended';
                    $actionLabel = 'Ver Antenas Suspendidas';
                    $actionUrl = 'index.php?url=antenas';
                    break;
                case 'new_payment':
                    $action = 'new_payment';
                    $actionLabel = 'Registrar Pago';
                    $actionUrl = 'index.php?url=payments';
                    break;
                case 'new_client':
                    $action = 'new_client';
                    $actionLabel = 'Registrar Cliente';
                    $actionUrl = 'index.php?url=clients';
                    break;
                case 'manual':
                    $action = 'open_manual';
                    $actionLabel = 'Abrir Manual Completo';
                    $actionUrl = 'index.php?url=manual';
                    break;
            }
        }

        return [
            'success' => true,
            'mode' => 'ai',
            'reply' => $replyText,
            'action' => $action,
            'actionLabel' => $actionLabel,
            'actionUrl' => $actionUrl
        ];
    }
}

