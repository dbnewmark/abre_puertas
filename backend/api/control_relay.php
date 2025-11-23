<?php
/**
 * API para controlar el relé de un dispositivo
 */

require_once __DIR__ . '/common.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendResponse(['error' => 'Método no permitido'], 405);
}

$user = getAuthenticatedUser();
$data = getJsonInput();

if (!isset($data['device_id']) || !isset($data['action'])) {
    sendResponse(['error' => 'device_id y action son requeridos'], 400);
}

$deviceId = $data['device_id'];
$action = strtolower($data['action']);

if (!in_array($action, ['on', 'off', 'toggle'])) {
    sendResponse(['error' => 'action debe ser: on, off, o toggle'], 400);
}

$db = Database::getInstance()->getConnection();

// Verificar que el usuario tiene acceso al dispositivo
$stmt = $db->prepare(
    "SELECT d.id, d.relay_state
     FROM devices d
     LEFT JOIN device_authorized_users dau ON d.id = dau.device_id
     WHERE d.id = ? AND (d.owner_id = ? OR dau.user_id = ?)"
);
$stmt->execute([$deviceId, $user['id'], $user['id']]);
$device = $stmt->fetch();

if (!$device) {
    sendResponse(['error' => 'Dispositivo no encontrado o acceso denegado'], 403);
}

// Determinar nuevo estado del relé
$newState = null;
if ($action === 'on') {
    $newState = 1;
} elseif ($action === 'off') {
    $newState = 0;
} elseif ($action === 'toggle') {
    $newState = $device['relay_state'] ? 0 : 1;
}

// Actualizar estado del relé
$stmt = $db->prepare("UPDATE devices SET relay_state = ? WHERE id = ?");
$stmt->execute([$newState, $deviceId]);

// Registrar comando en el log
$stmt = $db->prepare(
    "INSERT INTO commands (device_id, user_id, command) VALUES (?, ?, ?)"
);
$stmt->execute([$deviceId, $user['id'], $action]);

sendResponse([
    'success' => true,
    'relay_state' => $newState,
    'message' => 'Comando enviado exitosamente'
]);
