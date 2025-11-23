# Diagrama de Arquitectura del Sistema

## Vista General del Sistema

```
┌─────────────────────────────────────────────────────────────────┐
│                         USUARIO FINAL                           │
│                    (Navegador Web / Móvil)                     │
└────────────────────────────┬────────────────────────────────────┘
                             │
                             │ HTTPS/HTTP
                             │
┌────────────────────────────▼────────────────────────────────────┐
│                       SERVIDOR WEB                              │
│                    (Apache / Nginx)                             │
├─────────────────────────────────────────────────────────────────┤
│                                                                 │
│  ┌─────────────────────────────────────────────────────────┐  │
│  │              FRONTEND (HTML/CSS/JS)                     │  │
│  │  • login.html                                           │  │
│  │  • register.html                                        │  │
│  │  • index.html (Dashboard)                               │  │
│  │  • test.html                                            │  │
│  └─────────────────────────────────────────────────────────┘  │
│                            │                                    │
│                            │ AJAX/Fetch                         │
│                            │                                    │
│  ┌─────────────────────────▼───────────────────────────────┐  │
│  │              BACKEND REST API (PHP)                     │  │
│  │  ┌────────────────────────────────────────────────┐     │  │
│  │  │  Autenticación                                 │     │  │
│  │  │  • register.php                                │     │  │
│  │  │  • login.php                                   │     │  │
│  │  │  • logout.php                                  │     │  │
│  │  └────────────────────────────────────────────────┘     │  │
│  │  ┌────────────────────────────────────────────────┐     │  │
│  │  │  Gestión de Dispositivos                       │     │  │
│  │  │  • register_device.php                         │     │  │
│  │  │  • list_devices.php                            │     │  │
│  │  │  • control_relay.php                           │     │  │
│  │  └────────────────────────────────────────────────┘     │  │
│  │  ┌────────────────────────────────────────────────┐     │  │
│  │  │  Endpoints ESP32                               │     │  │
│  │  │  • device_status.php                           │     │  │
│  │  │  • report_status.php                           │     │  │
│  │  └────────────────────────────────────────────────┘     │  │
│  └─────────────────────────────────────────────────────────┘  │
│                            │                                    │
│                            │ PDO                                │
│                            │                                    │
│  ┌─────────────────────────▼───────────────────────────────┐  │
│  │                  BASE DE DATOS                          │  │
│  │                  (MariaDB 10.3+)                        │  │
│  │  ┌─────────────────────────────────────────────────┐    │  │
│  │  │  Tablas:                                        │    │  │
│  │  │  • users (usuarios del sistema)                 │    │  │
│  │  │  • devices (dispositivos ESP32)                 │    │  │
│  │  │  • sessions (tokens de autenticación)           │    │  │
│  │  │  • device_authorized_users (permisos)           │    │  │
│  │  │  • commands (log de acciones)                   │    │  │
│  │  └─────────────────────────────────────────────────┘    │  │
│  └─────────────────────────────────────────────────────────┘  │
└─────────────────────────────────────────────────────────────────┘
                             │
                             │ HTTP (JSON)
                             │ Polling cada 5s
                             │
┌────────────────────────────▼────────────────────────────────────┐
│                      INTERNET / WiFi                            │
└────────────────────────────┬────────────────────────────────────┘
                             │
┌────────────────────────────▼────────────────────────────────────┐
│                      DISPOSITIVO ESP32                          │
│  ┌──────────────────────────────────────────────────────────┐  │
│  │  Firmware (Arduino C++)                                  │  │
│  │  • Conexión WiFi 2.4GHz                                  │  │
│  │  • Polling device_status.php (cada 5s)                   │  │
│  │  • Control GPIO (Pin 2)                                  │  │
│  │  • Reporte de estado                                     │  │
│  └──────────────────────────────────────────────────────────┘  │
│                            │                                    │
│                            │ GPIO                               │
│                            │                                    │
│  ┌─────────────────────────▼───────────────────────────────┐  │
│  │           MÓDULO RELÉ 1 CANAL                           │  │
│  │  • IN   ← GPIO 2                                        │  │
│  │  • VCC  ← 3.3V / 5V                                     │  │
│  │  • GND  ← GND                                           │  │
│  └─────────────────────────────────────────────────────────┘  │
│                            │                                    │
│                            │ Contactos                          │
│                            │                                    │
│  ┌─────────────────────────▼───────────────────────────────┐  │
│  │        DISPOSITIVO A CONTROLAR                          │  │
│  │  (Puerta, luz, motor, electroimán, etc.)               │  │
│  └─────────────────────────────────────────────────────────┘  │
└─────────────────────────────────────────────────────────────────┘
```

## Flujo de Datos - Control de Relé

```
Usuario hace clic en "Encender"
    │
    ▼
[Dashboard] Envía POST a control_relay.php
    │
    ▼
[Backend API] Valida token de sesión
    │
    ▼
[Backend API] Actualiza relay_state=1 en DB
    │
    ▼
[MariaDB] Guarda nuevo estado
    │
    │ (Hasta 5 segundos después...)
    │
    ▼
[ESP32] Hace GET a device_status.php
    │
    ▼
[Backend API] Lee relay_state de DB
    │
    ▼
[Backend API] Devuelve {"relay_state": 1}
    │
    ▼
[ESP32] Recibe estado=1
    │
    ▼
[ESP32] Activa GPIO 2 (digitalWrite HIGH)
    │
    ▼
[Módulo Relé] Cierra contacto
    │
    ▼
[Dispositivo] Se enciende ✓
    │
    ▼
[ESP32] Envía POST a report_status.php
    │
    ▼
[MariaDB] Actualiza last_seen e is_online
    │
    │ (10 segundos después...)
    │
    ▼
[Dashboard] Auto-refresh consulta list_devices.php
    │
    ▼
[Frontend] Muestra relé en estado "Encendido" 🟢
```

## Flujo de Autenticación

```
┌─────────────────────────────────────────────────────────────┐
│                     REGISTRO USUARIO                        │
└─────────────────────────────────────────────────────────────┘
Usuario completa formulario → register.php
    │
    ▼
Valida email y contraseña (min 6 chars)
    │
    ▼
Hash contraseña con PASSWORD_DEFAULT
    │
    ▼
Inserta en tabla 'users'
    │
    ▼
Redirige a login.html

┌─────────────────────────────────────────────────────────────┐
│                    INICIO DE SESIÓN                         │
└─────────────────────────────────────────────────────────────┘
Usuario ingresa credenciales → login.php
    │
    ▼
Busca usuario por email
    │
    ▼
Verifica contraseña con password_verify()
    │
    ▼
Genera token (random_bytes(32))
    │
    ▼
Guarda en tabla 'sessions' (expires: +7 días)
    │
    ▼
Devuelve token al frontend
    │
    ▼
Frontend guarda en localStorage
    │
    ▼
Incluye en header: Authorization: Bearer <token>
```

## Flujo de Registro de ESP32

```
┌─────────────────────────────────────────────────────────────┐
│              PRIMER ARRANQUE DEL ESP32                      │
└─────────────────────────────────────────────────────────────┘
ESP32 arranca → Conecta WiFi
    │
    ▼
Obtiene Chip ID único (getChipID)
    │
    ▼
Intenta consultar device_status.php?chip_id=XXXXX
    │
    ▼
Backend devuelve: "Dispositivo no registrado"
    │
    │ (Usuario ve error en Monitor Serie)
    │
    ▼
Usuario copia Chip ID del Monitor Serie

┌─────────────────────────────────────────────────────────────┐
│           REGISTRO EN EL FRONTEND                           │
└─────────────────────────────────────────────────────────────┘
Usuario inicia sesión en dashboard
    │
    ▼
Ingresa Chip ID en formulario
    │
    ▼
POST register_device.php
    │
    ▼
Backend crea registro en tabla 'devices'
    │   • chip_id = XXXXX
    │   • owner_id = user.id
    │   • relay_state = 0
    │
    ▼
Backend crea registro en 'device_authorized_users'
    │
    ▼
Dispositivo queda registrado ✓

┌─────────────────────────────────────────────────────────────┐
│            PRÓXIMA CONSULTA DEL ESP32                       │
└─────────────────────────────────────────────────────────────┘
ESP32 consulta device_status.php (5s después)
    │
    ▼
Backend encuentra dispositivo
    │
    ▼
Devuelve: {"success": true, "relay_state": 0}
    │
    ▼
ESP32 funciona correctamente ✓
```

## Arquitectura de Seguridad

```
┌─────────────────────────────────────────────────────────────┐
│                   CAPA DE SEGURIDAD                         │
└─────────────────────────────────────────────────────────────┘

Frontend:
  • Token almacenado en localStorage
  • Enviado en header Authorization
  • Limpiar al cerrar sesión

Backend:
  • Validación de token en cada request
  • Tokens expiran en 7 días
  • Limpieza automática de tokens expirados
  
Base de Datos:
  • Contraseñas hasheadas (PASSWORD_DEFAULT)
  • PDO prepared statements (anti SQL injection)
  • Relaciones con foreign keys
  
Red:
  • CORS configurable
  • HTTPS recomendado en producción
  
ESP32:
  • Autenticación por Chip ID
  • Solo dispositivos registrados pueden consultar
  • HTTP (considerar HTTPS en producción)
```

## Escalabilidad

```
┌─────────────────────────────────────────────────────────────┐
│              CAPACIDAD DEL SISTEMA                          │
└─────────────────────────────────────────────────────────────┘

Usuarios:          Ilimitados*
Dispositivos:      Ilimitados*
Usuarios por       Múltiples (tabla device_authorized_users)
  dispositivo:
Dispositivos       Ilimitados*
  por usuario:
Requests por       ~12 por minuto (polling 5s)
  ESP32:
Latencia control:  0-5 segundos (polling)

* Limitado por recursos del servidor

┌─────────────────────────────────────────────────────────────┐
│              OPTIMIZACIONES POSIBLES                        │
└─────────────────────────────────────────────────────────────┘

Para más dispositivos:
  • Implementar WebSockets / MQTT
  • Caché con Redis
  • Load balancer
  • Base de datos replicada

Para menor latencia:
  • Push notifications en lugar de polling
  • WebSockets bidireccionales
  • Reducir intervalo de polling (con cuidado)
```

## Estructura de Carpetas

```
abre_puertas/
│
├── backend/                    # Backend PHP
│   ├── api/                    # Endpoints REST
│   │   ├── common.php          # Utilidades comunes
│   │   ├── register.php        # Registro usuario
│   │   ├── login.php           # Login
│   │   ├── logout.php          # Logout
│   │   ├── register_device.php # Registro ESP32
│   │   ├── list_devices.php    # Lista dispositivos
│   │   ├── control_relay.php   # Control relé
│   │   ├── device_status.php   # Estado para ESP32
│   │   └── report_status.php   # Reporte ESP32
│   │
│   └── config/                 # Configuración
│       ├── Database.php        # Clase DB
│       ├── Auth.php            # Autenticación
│       └── database.example.php # Template config
│
├── frontend/                   # Frontend web
│   ├── css/
│   │   └── style.css           # Estilos
│   ├── js/
│   │   ├── config.js           # Config API
│   │   ├── auth.js             # Login
│   │   ├── register.js         # Registro
│   │   └── dashboard.js        # Dashboard
│   ├── login.html              # Página login
│   ├── register.html           # Página registro
│   ├── index.html              # Dashboard
│   └── test.html               # Test conectividad
│
├── firmware/                   # Firmware ESP32
│   └── esp32_relay_control.ino # Arduino sketch
│
├── database/                   # Base de datos
│   └── schema.sql              # Schema MariaDB
│
├── .gitignore                  # Git ignore
├── README.md                   # Descripción
├── INSTALLATION.md             # Instalación
├── API_DOCUMENTATION.md        # API docs
├── QUICKSTART.md               # Guía rápida
├── PROJECT_SUMMARY.md          # Resumen
└── ARCHITECTURE.md             # Este archivo
```

## Tecnologías Utilizadas

```
┌─────────────────────────────────────────────────────────────┐
│                        STACK                                │
└─────────────────────────────────────────────────────────────┘

Backend:
  • PHP 7.4+
  • PDO (PHP Data Objects)
  • JSON

Base de Datos:
  • MariaDB 10.3+
  • InnoDB engine
  • UTF8MB4 charset

Frontend:
  • HTML5
  • CSS3
  • JavaScript ES6+ (vanilla, sin frameworks)

Firmware:
  • Arduino C++
  • ESP32 SDK
  • WiFi library
  • HTTPClient library

Servidor Web:
  • Apache 2.4+ o Nginx 1.18+
  • mod_rewrite (Apache)
  • PHP-FPM (Nginx)

Protocolos:
  • HTTP/HTTPS
  • REST
  • JSON
```

---

**Este diagrama muestra la arquitectura completa del sistema de control de relés ESP32.**
