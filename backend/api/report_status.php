<?php
/**
 * API para que el ESP32 reporte su estado actual
 */

require_once __DIR__ . '/common.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendResponse(['error' => 'Método no permitido'], 405);
}

$data = getJsonInput();

if (!isset($data['chip_id'])) {
    sendResponse(['error' => 'chip_id es requerido'], 400);
}

$chipId = $data['chip_id'];
$relayState = isset($data['relay_state']) ? (int)$data['relay_state'] : null;

$db = Database::getInstance()->getConnection();

// Buscar el dispositivo
$stmt = $db->prepare("SELECT id FROM devices WHERE chip_id = ?");
$stmt->execute([$chipId]);
$device = $stmt->fetch();

if (!$device) {
    sendResponse(['error' => 'Dispositivo no registrado'], 404);
}

// Actualizar estado
$updates = ['is_online = 1', 'last_seen = NOW()'];
$params = [];

if ($relayState !== null) {
    $updates[] = 'relay_state = ?';
    $params[] = $relayState;
}

$params[] = $device['id'];

$stmt = $db->prepare(
    "UPDATE devices SET " . implode(', ', $updates) . " WHERE id = ?"
);
$stmt->execute($params);

sendResponse([
    'success' => true,
    'message' => 'Estado actualizado'
]);
