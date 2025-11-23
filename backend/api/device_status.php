<?php
/**
 * API para que el ESP32 obtenga el estado del relé
 * El ESP32 debe llamar a este endpoint periódicamente para sincronizar el estado
 */

require_once __DIR__ . '/common.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    sendResponse(['error' => 'Método no permitido'], 405);
}

// El ESP32 envía su chip_id como parámetro
if (!isset($_GET['chip_id'])) {
    sendResponse(['error' => 'chip_id es requerido'], 400);
}

$chipId = $_GET['chip_id'];
$db = Database::getInstance()->getConnection();

// Buscar el dispositivo
$stmt = $db->prepare("SELECT id, relay_state, is_online FROM devices WHERE chip_id = ?");
$stmt->execute([$chipId]);
$device = $stmt->fetch();

if (!$device) {
    sendResponse(['error' => 'Dispositivo no registrado'], 404);
}

// Actualizar estado online y last_seen
$stmt = $db->prepare("UPDATE devices SET is_online = 1, last_seen = NOW() WHERE id = ?");
$stmt->execute([$device['id']]);

sendResponse([
    'success' => true,
    'relay_state' => (int)$device['relay_state'],
    'device_id' => $device['id']
]);
