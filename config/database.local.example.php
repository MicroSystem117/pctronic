<?php
/**
 * Copia este archivo como `database.local.php` dentro de la carpeta `config/`
 * en tu VPS y escribe aquí las credenciales que creaste en phpMyAdmin / aaPanel.
 *
 * El archivo `database.local.php` está en .gitignore para que no sobreescriba
 * tus datos locales al actualizar el proyecto ni exponga contraseñas en Git.
 */
return [
    'host'     => 'localhost',
    'db_name'  => 'Starlink_PCtronic', // Ojo: en Linux MySQL distingue mayúsculas/minúsculas
    'username' => 'root',              // O el usuario de base de datos creado en el panel
    'password' => '',                  // La contraseña de tu base de datos en el VPS
    'port'     => '3306',
];
