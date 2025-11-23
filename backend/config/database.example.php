<?php
/**
 * Configuración de conexión a la base de datos
 * 
 * IMPORTANTE: Copie este archivo a database.php y configure sus credenciales
 * NO suba database.php al repositorio (está en .gitignore)
 */

return [
    'host' => 'localhost',
    'database' => 'abre_puertas',
    'username' => 'root',
    'password' => '',
    'charset' => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',
    'options' => [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]
];
