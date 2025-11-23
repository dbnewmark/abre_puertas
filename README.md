# Abre Puertas - Sistema de Control de Relés ESP32

Sistema completo para controlar relés conectados a placas ESP32 a través de internet.

## Características

- Backend en PHP con MariaDB
- Frontend en HTML/CSS/JavaScript
- Firmware para ESP32
- Control de relés via internet
- Gestión de usuarios y dispositivos
- Autenticación y autorización

## Estructura del Proyecto

```
/backend          - API REST en PHP
/frontend         - Interfaz web HTML
/firmware         - Código para ESP32
/database         - Scripts SQL para MariaDB
```

## Instalación

### Requisitos Previos

- Servidor web con PHP 7.4+ y soporte para MariaDB
- MariaDB 10.3+
- Placa ESP32
- Arduino IDE o PlatformIO para cargar el firmware

### Configuración del Backend

1. Configurar la base de datos ejecutando los scripts en `/database`
2. Configurar las credenciales en `/backend/config/database.php`
3. Apuntar el servidor web a la carpeta `/backend`

### Configuración del Frontend

1. Apuntar el servidor web a la carpeta `/frontend`
2. Configurar la URL del API en `/frontend/js/config.js`

### Configuración del ESP32

1. Abrir el firmware en Arduino IDE
2. Configurar las credenciales WiFi y URL del servidor
3. Cargar el firmware a la placa ESP32

## Uso

1. Acceder al frontend via navegador web
2. Registrar una cuenta de usuario
3. Registrar el dispositivo ESP32 usando su Chip ID
4. Controlar el relé desde la interfaz web

## Tecnologías

- **Backend**: PHP, MariaDB
- **Frontend**: HTML5, CSS3, JavaScript
- **Firmware**: C++ para ESP32
- **Comunicación**: HTTP/REST API
