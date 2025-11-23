<?php
/**
 * API de inicio de sesión
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
$result = $auth->login($data['email'], $data['password']);

if ($result['success']) {
    sendResponse([
        'success' => true,
        'token' => $result['token'],
        'user' => $result['user']
    ]);
} else {
    sendResponse([
        'success' => false,
        'error' => $result['error']
    ], 401);
}
