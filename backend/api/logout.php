<?php
/**
 * API de cierre de sesión
 */

require_once __DIR__ . '/common.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendResponse(['error' => 'Método no permitido'], 405);
}

$user = getAuthenticatedUser();

$headers = getallheaders();
$token = null;
if (isset($headers['Authorization'])) {
    preg_match('/Bearer\s+(.*)$/i', $headers['Authorization'], $matches);
    $token = $matches[1] ?? null;
}

if ($token) {
    $auth = new Auth();
    $auth->logout($token);
}

sendResponse(['success' => true, 'message' => 'Sesión cerrada']);
