<?php
/**
 * Utilidades de autenticación
 */

require_once __DIR__ . '/Database.php';

class Auth {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Registrar nuevo usuario
     */
    public function register($email, $password, $name = null) {
        // Validar email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'error' => 'Email inválido'];
        }

        // Validar longitud de contraseña
        if (strlen($password) < 6) {
            return ['success' => false, 'error' => 'La contraseña debe tener al menos 6 caracteres'];
        }

        // Verificar si el usuario ya existe
        $stmt = $this->db->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            return ['success' => false, 'error' => 'El email ya está registrado'];
        }

        // Hash de la contraseña
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

        // Insertar usuario
        try {
            $stmt = $this->db->prepare(
                "INSERT INTO users (email, password_hash, name) VALUES (?, ?, ?)"
            );
            $stmt->execute([$email, $passwordHash, $name]);
            
            return [
                'success' => true,
                'user_id' => $this->db->lastInsertId()
            ];
        } catch (PDOException $e) {
            return ['success' => false, 'error' => 'Error al registrar usuario'];
        }
    }

    /**
     * Iniciar sesión
     */
    public function login($email, $password) {
        $stmt = $this->db->prepare("SELECT id, password_hash, name FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['password_hash'])) {
            return ['success' => false, 'error' => 'Credenciales inválidas'];
        }

        // Generar token de sesión
        $token = bin2hex(random_bytes(32));
        $expiresAt = date('Y-m-d H:i:s', strtotime('+7 days'));

        $stmt = $this->db->prepare(
            "INSERT INTO sessions (user_id, token, expires_at) VALUES (?, ?, ?)"
        );
        $stmt->execute([$user['id'], $token, $expiresAt]);

        return [
            'success' => true,
            'token' => $token,
            'user' => [
                'id' => $user['id'],
                'email' => $email,
                'name' => $user['name']
            ]
        ];
    }

    /**
     * Verificar token de sesión
     */
    public function verifyToken($token) {
        $stmt = $this->db->prepare(
            "SELECT s.user_id, u.email, u.name 
             FROM sessions s 
             JOIN users u ON s.user_id = u.id 
             WHERE s.token = ? AND s.expires_at > NOW()"
        );
        $stmt->execute([$token]);
        $session = $stmt->fetch();

        if (!$session) {
            return null;
        }

        return [
            'id' => $session['user_id'],
            'email' => $session['email'],
            'name' => $session['name']
        ];
    }

    /**
     * Cerrar sesión
     */
    public function logout($token) {
        $stmt = $this->db->prepare("DELETE FROM sessions WHERE token = ?");
        $stmt->execute([$token]);
        return ['success' => true];
    }

    /**
     * Limpiar sesiones expiradas
     */
    public function cleanExpiredSessions() {
        $stmt = $this->db->prepare("DELETE FROM sessions WHERE expires_at < NOW()");
        $stmt->execute();
    }
}
