<?php
/**
 * API para registrar un dispositivo ESP32
 */

require_once __DIR__ . '/common.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendResponse(['error' => 'Método no permitido'], 405);
}

$user = getAuthenticatedUser();
$data = getJsonInput();

if (!isset($data['chip_id'])) {
    sendResponse(['error' => 'chip_id es requerido'], 400);
}

$chipId = trim($data['chip_id']);
$alias = $data['alias'] ?? "Dispositivo $chipId";

$db = Database::getInstance()->getConnection();

// Verificar si el dispositivo ya existe
$stmt = $db->prepare("SELECT id, owner_id FROM devices WHERE chip_id = ?");
$stmt->execute([$chipId]);
$existingDevice = $stmt->fetch();

if ($existingDevice) {
    if ($existingDevice['owner_id'] == $user['id']) {
        sendResponse([
            'success' => true,
            'message' => 'Dispositivo ya registrado por este usuario',
            'device_id' => $existingDevice['id']
        ]);
    } else {
        sendResponse(['error' => 'Dispositivo ya registrado por otro usuario'], 403);
    }
}

// Registrar nuevo dispositivo
try {
    $stmt = $db->prepare(
        "INSERT INTO devices (chip_id, alias, owner_id) VALUES (?, ?, ?)"
    );
    $stmt->execute([$chipId, $alias, $user['id']]);
    $deviceId = $db->lastInsertId();

    // Agregar al usuario como autorizado
    $stmt = $db->prepare(
        "INSERT INTO device_authorized_users (device_id, user_id) VALUES (?, ?)"
    );
    $stmt->execute([$deviceId, $user['id']]);

    sendResponse([
        'success' => true,
        'message' => 'Dispositivo registrado exitosamente',
        'device_id' => $deviceId
    ], 201);
} catch (PDOException $e) {
    sendResponse(['error' => 'Error al registrar dispositivo'], 500);
}
