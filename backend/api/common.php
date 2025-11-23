<?php
/**
 * Headers comunes para todas las APIs
 */

// Permitir CORS
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json; charset=UTF-8');

// Manejar preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../config/Auth.php';

/**
 * Enviar respuesta JSON
 */
function sendResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    echo json_encode($data);
    exit();
}

/**
 * Obtener datos JSON del request
 */
function getJsonInput() {
    $input = file_get_contents('php://input');
    return json_decode($input, true);
}

/**
 * Obtener usuario autenticado
 */
function getAuthenticatedUser() {
    $headers = getallheaders();
    $token = null;

    // Buscar token en Authorization header
    if (isset($headers['Authorization'])) {
        $matches = [];
        if (preg_match('/Bearer\s+(.*)$/i', $headers['Authorization'], $matches)) {
            $token = $matches[1];
        }
    }

    // También buscar en parámetro GET para compatibilidad con ESP32
    if (!$token && isset($_GET['token'])) {
        $token = $_GET['token'];
    }

    if (!$token) {
        sendResponse(['error' => 'Token no proporcionado'], 401);
    }

    $auth = new Auth();
    $user = $auth->verifyToken($token);

    if (!$user) {
        sendResponse(['error' => 'Token inválido o expirado'], 401);
    }

    return $user;
}
