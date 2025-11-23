<?php
/**
 * API de registro de usuario
 */

require_once __DIR__ . '/common.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendResponse(['error' => 'Método no permitido'], 405);
}

$data = getJsonInput();

if (!isset($data['email']) || !isset($data['password'])) {
    sendResponse(['error' => 'Email y contraseña son requeridos'], 400);
}

$auth = new Auth();
$result = $auth->register(
    $data['email'],
    $data['password'],
    $data['name'] ?? null
);

if ($result['success']) {
    sendResponse([
        'success' => true,
        'message' => 'Usuario registrado exitosamente',
        'user_id' => $result['user_id']
    ], 201);
} else {
    sendResponse([
        'success' => false,
        'error' => $result['error']
    ], 400);
}
