<?php

/**
 * Motor de Autenticación en Dos Pasos (TOTP - RFC 6238)
 * Implementación pura en PHP sin dependencias de Composer.
 * Compatible con Google Authenticator, Authy, Microsoft Authenticator, 1Password, etc.
 */
class TOTP {
    private static $base32Chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';

    /**
     * Generar un secreto aleatorio de 16 caracteres en Base32 (80 bits)
     */
    public static function generateSecret($length = 16) {
        $secret = '';
        $randomBytes = random_bytes($length);
        for ($i = 0; $i < $length; $i++) {
            $secret .= self::$base32Chars[ord($randomBytes[$i]) & 31];
        }
        return $secret;
    }

    /**
     * Calcular el código TOTP de 6 dígitos para un secreto y un paso de tiempo dado
     */
    public static function getCode($secret, $timeSlice = null) {
        if ($timeSlice === null) {
            $timeSlice = floor(time() / 30);
        }

        $secretKey = self::base32Decode($secret);
        if ($secretKey === false) {
            return false;
        }

        // Empaquetar el contador en formato binario de 64 bits (Big Endian)
        $time = pack('N*', 0) . pack('N*', $timeSlice);

        // Generar hash HMAC-SHA1
        $hmac = hash_hmac('sha1', $time, $secretKey, true);

        // Truncamiento dinámico (RFC 4226)
        $offset = ord(substr($hmac, -1)) & 0x0F;
        $hashPart = substr($hmac, $offset, 4);
        $value = unpack('N', $hashPart);
        $value = $value[1] & 0x7FFFFFFF;

        $modulo = pow(10, 6);
        return str_pad($value % $modulo, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Verificar un código TOTP con una ventana de tolerancia de +/- 1 paso (30s de margen)
     */
    public static function verifyCode($secret, $code, $discrepancy = 1) {
        if (strlen((string)$code) !== 6) {
            return false;
        }

        $currentTimeSlice = floor(time() / 30);
        for ($i = -$discrepancy; $i <= $discrepancy; $i++) {
            $calculatedCode = self::getCode($secret, $currentTimeSlice + $i);
            if ($calculatedCode !== false && hash_equals((string)$calculatedCode, (string)$code)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Generar la URI otpauth:// para escanear en apps autenticadoras
     */
    public static function getOtpAuthUrl($accountName, $secret, $issuer = 'Starlink PCtronic') {
        $issuerEncoded = rawurlencode($issuer);
        $accountEncoded = rawurlencode($accountName);
        return "otpauth://totp/{$issuerEncoded}:{$accountEncoded}?secret={$secret}&issuer={$issuerEncoded}&algorithm=SHA1&digits=6&period=30";
    }

    /**
     * Decodificar una cadena Base32 a binario
     */
    private static function base32Decode($secret) {
        $secret = strtoupper(trim($secret));
        if (empty($secret)) {
            return false;
        }

        $buffer = 0;
        $bufferSize = 0;
        $binary = '';

        for ($i = 0; $i < strlen($secret); $i++) {
            $char = $secret[$i];
            $val = strpos(self::$base32Chars, $char);
            if ($val === false) {
                return false;
            }

            $buffer = ($buffer << 5) | $val;
            $bufferSize += 5;

            if ($bufferSize >= 8) {
                $bufferSize -= 8;
                $binary .= chr(($buffer >> $bufferSize) & 0xFF);
            }
        }

        return $binary;
    }
}
