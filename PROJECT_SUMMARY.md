# Sistema de Control de Relés ESP32 - Resumen Técnico

## 🎯 Objetivo del Proyecto

Sistema completo de control remoto de relés conectados a placas ESP32 mediante internet, con interfaz web y base de datos.

## 📋 Componentes del Sistema

### 1. Backend (PHP + MariaDB)
- **Lenguaje**: PHP 7.4+
- **Base de datos**: MariaDB 10.3+
- **API**: REST con JSON
- **Autenticación**: Tokens de sesión (7 días)
- **Seguridad**: PASSWORD_DEFAULT, PDO prepared statements

### 2. Frontend (HTML/CSS/JavaScript)
- **Tecnología**: HTML5, CSS3, JavaScript vanilla (sin frameworks)
- **Páginas**: Login, Registro, Dashboard
- **Características**: Responsive, auto-refresh, control en tiempo real

### 3. Firmware ESP32 (Arduino C++)
- **Plataforma**: ESP32 (Arduino IDE)
- **Conectividad**: WiFi 2.4GHz
- **Protocolo**: HTTP REST
- **Polling**: Cada 5 segundos
- **Control**: GPIO para relés

## 🗂️ Estructura de Archivos

```
/backend
  /api
    common.php              - Utilidades y CORS
    register.php            - Registro de usuarios
    login.php               - Inicio de sesión
    logout.php              - Cierre de sesión
    register_device.php     - Registro de ESP32
    list_devices.php        - Lista dispositivos
    control_relay.php       - Control del relé
    device_status.php       - Estado para ESP32
    report_status.php       - Reporte de ESP32
  /config
    Database.php            - Clase de conexión
    Auth.php                - Autenticación
    database.example.php    - Plantilla de config

/frontend
  /css
    style.css               - Estilos completos
  /js
    config.js               - Configuración API
    auth.js                 - Login
    register.js             - Registro
    dashboard.js            - Control y gestión
  login.html                - Página de login
  register.html             - Página de registro
  index.html                - Dashboard principal
  test.html                 - Test de conectividad

/firmware
  esp32_relay_control.ino   - Firmware ESP32

/database
  schema.sql                - Schema de MariaDB

Documentación:
  README.md                 - Descripción general
  INSTALLATION.md           - Guía de instalación
  API_DOCUMENTATION.md      - Referencia de API
  QUICKSTART.md             - Inicio rápido
  PROJECT_SUMMARY.md        - Este archivo
```

## 🔄 Flujo de Funcionamiento

```
1. Usuario se registra → MariaDB (tabla users)
2. Usuario inicia sesión → Recibe token (tabla sessions)
3. Usuario registra ESP32 → MariaDB (tabla devices) usando chip_id
4. Usuario envía comando → MariaDB (relay_state actualizado)
5. ESP32 consulta estado → GET device_status.php cada 5s
6. ESP32 actualiza GPIO → Relé físico activado/desactivado
7. ESP32 reporta estado → POST report_status.php
8. Dashboard auto-refresh → Muestra estado actualizado (cada 10s)
```

## 🗄️ Esquema de Base de Datos

### Tabla: users
- id, email, password_hash, name, created_at, updated_at

### Tabla: devices
- id, chip_id (único), alias, owner_id, is_online, last_seen, relay_state, created_at, updated_at

### Tabla: device_authorized_users
- device_id, user_id (relación muchos a muchos)

### Tabla: sessions
- id, user_id, token (único), expires_at, created_at

### Tabla: commands (log)
- id, device_id, user_id, command, executed_at

## 🔐 Seguridad Implementada

1. **Contraseñas**: Hasheadas con PASSWORD_DEFAULT (bcrypt)
2. **SQL Injection**: Protección con PDO prepared statements
3. **Tokens**: Generados con random_bytes(32), expiración 7 días
4. **CORS**: Configurable para producción
5. **HTTPS**: Recomendado (configurar en servidor)

## ⚡ Características Principales

### Backend
- ✅ Autenticación de usuarios con tokens
- ✅ Gestión de dispositivos ESP32
- ✅ Control de relés via API REST
- ✅ Log de comandos ejecutados
- ✅ Sistema de usuarios autorizados por dispositivo

### Frontend
- ✅ Interfaz responsive (mobile-friendly)
- ✅ Auto-refresh cada 10 segundos
- ✅ Control visual del estado del relé
- ✅ Gestión de múltiples dispositivos
- ✅ Indicadores de estado online/offline

### ESP32
- ✅ Conexión WiFi automática
- ✅ Polling cada 5 segundos
- ✅ Reporte de estado al servidor
- ✅ Control manual con botón BOOT
- ✅ Identificación única por Chip ID

## 🚀 Instalación Rápida

```bash
# 1. Base de datos
mysql -u root -p < database/schema.sql

# 2. Configurar backend
cd backend/config
cp database.example.php database.php
nano database.php  # Editar credenciales

# 3. Servidor web
# Apuntar DocumentRoot a la carpeta del proyecto

# 4. Frontend
# Editar frontend/js/config.js con la URL correcta

# 5. ESP32
# Editar firmware con WiFi y URL del servidor
# Cargar con Arduino IDE
```

## 📊 Endpoints de la API

| Endpoint | Método | Auth | Descripción |
|----------|--------|------|-------------|
| /register.php | POST | No | Registrar usuario |
| /login.php | POST | No | Iniciar sesión |
| /logout.php | POST | Sí | Cerrar sesión |
| /register_device.php | POST | Sí | Registrar ESP32 |
| /list_devices.php | GET | Sí | Listar dispositivos |
| /control_relay.php | POST | Sí | Controlar relé |
| /device_status.php | GET | No* | Estado para ESP32 |
| /report_status.php | POST | No* | Reporte de ESP32 |

*ESP32 usa chip_id en lugar de token

## 🔧 Configuración Requerida

### Servidor
- PHP 7.4+ con extensiones: PDO, PDO_MySQL, JSON
- MariaDB 10.3+
- Apache o Nginx

### ESP32
- Modelo: ESP32 Dev Module o compatible
- GPIO: Pin 2 para relé (configurable)
- WiFi: 2.4GHz

### Hardware
- Módulo relé 1 canal (3.3V o 5V)
- Fuente de alimentación
- Cables jumper

## 📱 URLs de Acceso

Después de la instalación:

- **Frontend principal**: `http://tudominio.com/frontend/index.html`
- **Login**: `http://tudominio.com/frontend/login.html`
- **Registro**: `http://tudominio.com/frontend/register.html`
- **Test**: `http://tudominio.com/frontend/test.html`

## 🐛 Troubleshooting Común

### ESP32 no conecta
- Verificar SSID/password
- WiFi debe ser 2.4GHz
- Verificar señal

### API no responde
- Verificar servidor PHP corriendo
- Revisar URL en config.js
- Verificar CORS

### Relé no cambia
- Verificar conexiones GPIO
- Revisar pin configurado
- Probar botón BOOT manual

### "Dispositivo no registrado"
- Verificar Chip ID correcto
- Registrar en dashboard primero
- Verificar URL del servidor en ESP32

## 📈 Métricas del Sistema

- **Latencia de control**: 0-5 segundos (polling ESP32)
- **Refresh dashboard**: 10 segundos
- **Timeout WiFi**: 10 segundos (20 intentos)
- **Expiración tokens**: 7 días
- **Dispositivos por usuario**: Ilimitado
- **Usuarios por dispositivo**: Múltiples (autorizados)

## 🔮 Posibles Mejoras Futuras

1. **WebSockets** para actualizaciones en tiempo real
2. **MQTT** para comunicación ESP32
3. **Notificaciones** push/email
4. **Automatización** con horarios
5. **Histórico** de activaciones
6. **Gráficos** de uso
7. **Multi-idioma** i18n
8. **App móvil** nativa
9. **Integración** con IoT platforms
10. **2FA** autenticación dos factores

## 📝 Notas para Desarrolladores

### Agregar un nuevo endpoint
1. Crear archivo PHP en `/backend/api/`
2. Incluir `common.php` para CORS y utilidades
3. Usar `getAuthenticatedUser()` si requiere auth
4. Devolver JSON con `sendResponse()`

### Modificar el esquema DB
1. Actualizar `/database/schema.sql`
2. Crear script de migración
3. Documentar cambios

### Cambiar el pin del relé
1. Editar `RELAY_PIN` en firmware (línea 12)
2. Recompilar y cargar firmware

### Personalizar frontend
1. Editar CSS en `/frontend/css/style.css`
2. Los colores principales usan #667eea

## 📄 Licencia y Créditos

Proyecto de código abierto para control de relés con ESP32.

---

**Versión**: 1.0  
**Última actualización**: 2024  
**Autor**: Sistema desarrollado para dbnewmark  
**Repositorio**: github.com/dbnewmark/abre_puertas
