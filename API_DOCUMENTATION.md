# Documentación de la API REST

## Descripción General

API REST para controlar relés conectados a placas ESP32 a través de internet.

**Base URL**: `/backend/api`

Todos los endpoints devuelven JSON. Los endpoints que requieren autenticación deben incluir el token en el header `Authorization: Bearer <token>`.

---

## Autenticación

### Registrar Usuario

**Endpoint**: `POST /register.php`

**Descripción**: Crea una nueva cuenta de usuario.

**Request Body**:
```json
{
  "email": "usuario@example.com",
  "password": "contraseña123",
  "name": "Nombre Usuario" // opcional
}
```

**Response** (201 Created):
```json
{
  "success": true,
  "message": "Usuario registrado exitosamente",
  "user_id": 1
}
```

**Errores**:
- `400`: Email inválido o contraseña muy corta
- `400`: Email ya registrado

---

### Iniciar Sesión

**Endpoint**: `POST /login.php`

**Descripción**: Autentica al usuario y devuelve un token de sesión.

**Request Body**:
```json
{
  "email": "usuario@example.com",
  "password": "contraseña123"
}
```

**Response** (200 OK):
```json
{
  "success": true,
  "token": "a1b2c3d4e5f6...",
  "user": {
    "id": 1,
    "email": "usuario@example.com",
    "name": "Nombre Usuario"
  }
}
```

**Errores**:
- `401`: Credenciales inválidas

---

### Cerrar Sesión

**Endpoint**: `POST /logout.php`

**Descripción**: Invalida el token de sesión actual.

**Headers**:
```
Authorization: Bearer <token>
```

**Response** (200 OK):
```json
{
  "success": true,
  "message": "Sesión cerrada"
}
```

---

## Gestión de Dispositivos

### Registrar Dispositivo

**Endpoint**: `POST /register_device.php`

**Descripción**: Registra un nuevo dispositivo ESP32.

**Headers**:
```
Authorization: Bearer <token>
```

**Request Body**:
```json
{
  "chip_id": "123456789ABC",
  "alias": "Puerta Principal" // opcional
}
```

**Response** (201 Created):
```json
{
  "success": true,
  "message": "Dispositivo registrado exitosamente",
  "device_id": 1
}
```

**Errores**:
- `400`: chip_id requerido
- `403`: Dispositivo ya registrado por otro usuario

---

### Listar Dispositivos

**Endpoint**: `GET /list_devices.php`

**Descripción**: Obtiene todos los dispositivos del usuario autenticado.

**Headers**:
```
Authorization: Bearer <token>
```

**Response** (200 OK):
```json
{
  "success": true,
  "devices": [
    {
      "id": 1,
      "chip_id": "123456789ABC",
      "alias": "Puerta Principal",
      "is_online": 1,
      "last_seen": "2024-01-15 10:30:45",
      "relay_state": 0,
      "owner_id": 1,
      "created_at": "2024-01-10 08:00:00",
      "is_owner": 1
    }
  ]
}
```

---

### Controlar Relé

**Endpoint**: `POST /control_relay.php`

**Descripción**: Envía un comando para controlar el estado del relé.

**Headers**:
```
Authorization: Bearer <token>
```

**Request Body**:
```json
{
  "device_id": 1,
  "action": "on" // "on", "off" o "toggle"
}
```

**Response** (200 OK):
```json
{
  "success": true,
  "relay_state": 1,
  "message": "Comando enviado exitosamente"
}
```

**Errores**:
- `400`: device_id y action requeridos
- `400`: action debe ser "on", "off" o "toggle"
- `403`: Dispositivo no encontrado o acceso denegado

---

## Endpoints para ESP32

### Obtener Estado del Relé

**Endpoint**: `GET /device_status.php?chip_id=<chip_id>`

**Descripción**: El ESP32 consulta el estado deseado del relé.

**Query Parameters**:
- `chip_id`: Identificador único del ESP32

**Response** (200 OK):
```json
{
  "success": true,
  "relay_state": 1,
  "device_id": 1
}
```

**Errores**:
- `400`: chip_id requerido
- `404`: Dispositivo no registrado

**Nota**: Este endpoint también actualiza `is_online=1` y `last_seen` del dispositivo.

---

### Reportar Estado

**Endpoint**: `POST /report_status.php`

**Descripción**: El ESP32 reporta su estado actual al servidor.

**Request Body**:
```json
{
  "chip_id": "123456789ABC",
  "relay_state": 1 // opcional
}
```

**Response** (200 OK):
```json
{
  "success": true,
  "message": "Estado actualizado"
}
```

**Errores**:
- `400`: chip_id requerido
- `404`: Dispositivo no registrado

---

## Códigos de Estado HTTP

- `200 OK`: Solicitud exitosa
- `201 Created`: Recurso creado exitosamente
- `400 Bad Request`: Datos inválidos o faltantes
- `401 Unauthorized`: Token no proporcionado o inválido
- `403 Forbidden`: Acceso denegado al recurso
- `404 Not Found`: Recurso no encontrado
- `405 Method Not Allowed`: Método HTTP no permitido
- `500 Internal Server Error`: Error del servidor

---

## Estructura de Respuesta de Error

Todas las respuestas de error incluyen:

```json
{
  "error": "Descripción del error"
}
```

O en caso de éxito fallido:

```json
{
  "success": false,
  "error": "Descripción del error"
}
```

---

## Autenticación del ESP32

El ESP32 **no** requiere autenticación con token. Solo necesita su `chip_id` que debe estar previamente registrado en el sistema por un usuario autenticado.

---

## CORS

La API está configurada para permitir solicitudes desde cualquier origen (`Access-Control-Allow-Origin: *`). 

Para producción, se recomienda restringir esto a dominios específicos editando `backend/api/common.php`:

```php
// Cambiar esta línea:
header('Access-Control-Allow-Origin: *');

// Por algo como:
header('Access-Control-Allow-Origin: https://tudominio.com');
```

---

## Seguridad

1. **Tokens de Sesión**: Válidos por 7 días. Se almacenan en la tabla `sessions`.
2. **Contraseñas**: Hasheadas con `PASSWORD_BCRYPT`.
3. **SQL Injection**: Protegido con prepared statements (PDO).
4. **HTTPS**: Se recomienda usar HTTPS en producción para cifrar las comunicaciones.

---

## Límites y Consideraciones

- **Tokens**: Expiran después de 7 días de inactividad
- **Consultas ESP32**: Recomendado cada 5-10 segundos
- **Estado Online**: Un dispositivo se considera offline si no consulta por > 1 minuto
- **Comandos**: Se ejecutan inmediatamente cuando el ESP32 consulta el estado

---

## Ejemplos de Uso

### Ejemplo con cURL - Login

```bash
curl -X POST http://tudominio.com/backend/api/login.php \
  -H "Content-Type: application/json" \
  -d '{"email":"usuario@example.com","password":"contraseña123"}'
```

### Ejemplo con cURL - Listar Dispositivos

```bash
curl -X GET http://tudominio.com/backend/api/list_devices.php \
  -H "Authorization: Bearer a1b2c3d4e5f6..."
```

### Ejemplo con cURL - Controlar Relé

```bash
curl -X POST http://tudominio.com/backend/api/control_relay.php \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer a1b2c3d4e5f6..." \
  -d '{"device_id":1,"action":"on"}'
```

### Ejemplo ESP32 - Consultar Estado

```bash
curl "http://tudominio.com/backend/api/device_status.php?chip_id=123456789ABC"
```
