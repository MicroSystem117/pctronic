<?php

function app_url($path = '') {
    $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (isset($_SERVER['SERVER_PORT']) && (int) $_SERVER['SERVER_PORT'] === 443)
        || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']) === 'https')
        || (!empty($_SERVER['HTTP_X_FORWARDED_SSL']) && strtolower($_SERVER['HTTP_X_FORWARDED_SSL']) === 'on');
    $scheme = $isHttps ? 'https' : 'http';
    $host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost';
    $basePath = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/'));
    $basePath = rtrim($basePath, '/.');

    return $scheme . '://' . $host . ($basePath === '' ? '' : $basePath)
        . '/' . ltrim($path, '/');
}

/**
 * Normaliza un número telefónico para la API de WhatsApp (wa.me).
 * Elimina caracteres no numéricos y antepone código de país si es necesario (ej: Venezuela 58).
 */
function format_whatsapp_phone($phone) {
    if (empty($phone)) return '';
    $digits = preg_replace('/\D+/', '', (string) $phone);
    if (empty($digits)) return '';

    // Si comienza con 0 (ej: 04141234567, 0424..., 0412...) -> reemplazar 0 por 58
    if (strpos($digits, '0') === 0) {
        $digits = '58' . substr($digits, 1);
    }
    // Si tiene 10 dígitos y empieza por 4 (ej: 4141234567) -> anteponer 58
    elseif (strlen($digits) === 10 && strpos($digits, '4') === 0) {
        $digits = '58' . $digits;
    }
    return $digits;
}

/**
 * Genera el enlace de WhatsApp con mensaje personalizado de recordatorio de pago.
 */
function build_whatsapp_reminder_url($clientName, $phone, $serial, $nickname = '', $planName = '', $planPrice = '', $dueDay = '', $status = 'Pendiente', $overdueMonths = 0, $overdueAmount = 0.0) {
    $cleanPhone = format_whatsapp_phone($phone);

    $nombreCliente = trim((string) $clientName);
    if (empty($nombreCliente)) {
        $nombreCliente = 'Estimado/a cliente';
    }

    $msg = "Hola *{$nombreCliente}*, le saludamos cordialmente de *PCTRONIC* 🛰️\n\n";

    $diaTexto = !empty($dueDay) ? "el día *{$dueDay}* de cada mes" : "en los próximos días";
    $msg .= "Le recordamos que la fecha de corte y pago de su servicio Starlink corresponde a {$diaTexto}.\n\n";

    $msg .= "📋 *Detalles del servicio:*\n";
    $msg .= "• *Serial:* `{$serial}`\n";
    if (!empty($nickname)) {
        $msg .= "• *Identificador:* {$nickname}\n";
    }
    if (!empty($planName)) {
        $msg .= "• *Plan:* {$planName}\n";
    }
    if (!empty($planPrice) && is_numeric($planPrice)) {
        $msg .= "• *Monto mensual:* $" . number_format((float) $planPrice, 2, ',', '.') . "\n";
    }

    $overdueMonths = intval($overdueMonths);
    $isAtrasado = strtolower((string) $status) === 'atrasado' || $overdueMonths > 0;

    if ($isAtrasado) {
        $msg .= "\n⚠️ *Estado:* *Atrasado*\n";
        if ($overdueMonths > 0) {
            $mesesTexto = $overdueMonths === 1 ? "1 mes pendiente" : "{$overdueMonths} meses pendientes";
            $msg .= "Presenta un acumulado de *{$mesesTexto}* por pagar";
            if ($overdueAmount > 0) {
                $msg .= " (Total estimado: *$" . number_format((float) $overdueAmount, 2, ',', '.') . "*)";
            }
            $msg .= ".\n";
        }
        $msg .= "Agradecemos reportar su pago a la brevedad para garantizar la continuidad del servicio.\n\n";
    } else {
        $msg .= "\n⏳ *Estado:* *Pendiente*\n";
        $msg .= "Por favor recuerde realizar su pago a tiempo para mantener su servicio activo.\n\n";
    }

    $msg .= "Si ya efectuó su pago, por favor ignore este recordatorio o compártanos su comprobante por este medio. ¡Muchas gracias! 🙏";

    if (!empty($cleanPhone)) {
        return 'https://wa.me/' . $cleanPhone . '?text=' . rawurlencode($msg);
    }
    return 'https://wa.me/?text=' . rawurlencode($msg);
}