<?php
/**
 * API para listar dispositivos del usuario
 */

require_once __DIR__ . '/common.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    sendResponse(['error' => 'Método no permitido'], 405);
}

$user = getAuthenticatedUser();
$db = Database::getInstance()->getConnection();

// Obtener todos los dispositivos del usuario (propios o autorizados)
$stmt = $db->prepare(
    "SELECT DISTINCT d.id, d.chip_id, d.alias, d.is_online, d.last_seen, d.relay_state,
            d.owner_id, d.created_at,
            (d.owner_id = ?) as is_owner
     FROM devices d
     LEFT JOIN device_authorized_users dau ON d.id = dau.device_id
     WHERE d.owner_id = ? OR dau.user_id = ?
     ORDER BY d.created_at DESC"
);
$stmt->execute([$user['id'], $user['id'], $user['id']]);
$devices = $stmt->fetchAll();

sendResponse([
    'success' => true,
    'devices' => $devices
]);
