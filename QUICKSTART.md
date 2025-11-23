# Guía Rápida de Inicio

Esta guía te ayudará a poner en marcha el sistema de control de relés ESP32 rápidamente.

## Para el Administrador del Servidor

### 1. Configurar la Base de Datos (5 minutos)

```bash
# Acceder a MariaDB
mysql -u root -p

# Ejecutar los comandos del schema
source /ruta/a/database/schema.sql

# Salir
exit
```

### 2. Configurar el Backend PHP (3 minutos)

```bash
# Copiar configuración
cd backend/config
cp database.example.php database.php

# Editar con tus credenciales
nano database.php
```

Cambiar:
```php
'host' => 'localhost',
'database' => 'abre_puertas',
'username' => 'root',
'password' => 'tu_password',
```

### 3. Configurar Servidor Web (ya debe estar configurado)

Si usas Apache, asegúrate de que el DocumentRoot apunte a la carpeta del proyecto.

### 4. Probar el Backend

Abre en el navegador:
```
http://tudominio.com/frontend/register.html
```

Si ves la página de registro, ¡el backend está funcionando! 🎉

---

## Para el Usuario Final

### 1. Crear tu Cuenta (2 minutos)

1. Ir a: `http://tudominio.com/frontend/register.html`
2. Llenar el formulario:
   - Nombre (opcional)
   - Email
   - Contraseña (mínimo 6 caracteres)
3. Clic en "Registrarse"

### 2. Iniciar Sesión

1. Ir a: `http://tudominio.com/frontend/login.html`
2. Ingresar email y contraseña
3. Clic en "Iniciar Sesión"

Serás redirigido al dashboard.

---

## Para el Instalador del ESP32

### 1. Preparar el Hardware (10 minutos)

#### Materiales necesarios:
- ESP32
- Módulo relé de 1 canal (3.3V o 5V)
- Cables jumper
- Cable USB
- Fuente de alimentación

#### Conexiones:

```
ESP32 Pin    →    Módulo Relé
━━━━━━━━━━━━━━━━━━━━━━━━━━━━
GPIO 2       →    IN (señal)
GND          →    GND
3.3V o 5V    →    VCC
```

### 2. Programar el ESP32 (15 minutos)

#### A. Instalar Arduino IDE y ESP32

1. Descargar Arduino IDE desde: https://www.arduino.cc/en/software
2. Abrir Arduino IDE
3. Ir a **Archivo > Preferencias**
4. Agregar en "URLs Adicionales de Tarjetas":
   ```
   https://dl.espressif.com/dl/package_esp32_index.json
   ```
5. Ir a **Herramientas > Placa > Gestor de tarjetas**
6. Buscar "ESP32" e instalar "ESP32 by Espressif Systems"

#### B. Configurar el Firmware

1. Abrir `firmware/esp32_relay_control.ino` en Arduino IDE
2. Modificar líneas 3-4 con tu WiFi:
   ```cpp
   const char* ssid = "MiWiFi";
   const char* password = "MiPassword123";
   ```
3. Modificar líneas 9-10 con la URL de tu servidor:
   ```cpp
   const char* serverUrl = "http://192.168.1.100/backend/api/device_status.php";
   const char* reportUrl = "http://192.168.1.100/backend/api/report_status.php";
   ```
   
   💡 **Tip**: Usa la IP local si estás en la misma red, o el dominio público si no.

#### C. Cargar el Firmware

1. Conectar el ESP32 al puerto USB
2. En Arduino IDE:
   - **Herramientas > Placa**: Seleccionar tu ESP32 (ej: "ESP32 Dev Module")
   - **Herramientas > Puerto**: Seleccionar el puerto COM/USB
   - **Herramientas > Upload Speed**: 115200
3. Clic en el botón "Subir" (→)
4. Esperar a que se complete (30-60 segundos)

#### D. Obtener el Chip ID

1. Abrir **Monitor Serie** (lupa en la esquina superior derecha)
2. Configurar velocidad a **115200 baud** (esquina inferior derecha)
3. Presionar botón **RESET** en el ESP32
4. Anotar el **Chip ID** que aparece:
   ```
   Chip ID: 123456789ABC
   ```

### 3. Registrar el ESP32 en el Sistema (2 minutos)

1. Ir al dashboard: `http://tudominio.com/frontend/index.html`
2. En "Registrar Nuevo Dispositivo":
   - **Chip ID**: Pegar el ID del paso anterior (ej: `123456789ABC`)
   - **Nombre**: Darle un nombre (ej: "Puerta Principal")
3. Clic en "Registrar"

¡Listo! El dispositivo debería aparecer en la lista de dispositivos.

### 4. Probar el Control (1 minuto)

En el dashboard, deberías ver tu dispositivo con:
- Estado: **En línea** (verde)
- Relé: **Apagado** (rojo)

Prueba los botones:
- **Encender**: El relé debería activarse (hacer "click")
- **Apagar**: El relé debería desactivarse
- **Alternar**: Cambia el estado actual

El ESP32 consulta el servidor cada 5 segundos, así que puede haber un pequeño retraso.

---

## Solución Rápida de Problemas

### ❌ "Error de conexión con el servidor"
- Verificar que el servidor web esté corriendo
- Verificar la URL en `frontend/js/config.js`
- Revisar la consola del navegador (F12) para más detalles

### ❌ "Dispositivo no registrado"
- Asegurar que el Chip ID es correcto
- Verificar que está registrado en el dashboard
- Revisar que la URL del servidor en el ESP32 es correcta

### ❌ ESP32 no se conecta al WiFi
- Verificar SSID y contraseña
- Asegurar que el WiFi es 2.4GHz (ESP32 no soporta 5GHz)
- Verificar señal WiFi

### ❌ El relé no responde
- Verificar conexiones físicas
- Verificar que el pin es el correcto (línea 12 del firmware)
- Probar presionando el botón BOOT del ESP32 (alterna el relé manualmente)

### ❌ Error al subir firmware al ESP32
- Mantener presionado el botón BOOT mientras se sube
- Verificar drivers USB del ESP32 instalados
- Probar con otro cable USB

---

## Próximos Pasos

Una vez que todo funciona:

1. **Seguridad**: Configurar HTTPS en tu servidor
2. **Red externa**: Configurar port forwarding en tu router si quieres acceso desde internet
3. **Automatización**: Puedes crear scripts que llamen a la API para automatizar acciones
4. **Múltiples dispositivos**: Registra más ESP32 repitiendo el proceso

---

## ¿Necesitas Ayuda?

Revisa la documentación completa:
- `INSTALLATION.md` - Guía de instalación detallada
- `API_DOCUMENTATION.md` - Referencia de la API
- `README.md` - Información general del proyecto

---

**¡Disfruta controlando tus dispositivos! 🚪🔌**
