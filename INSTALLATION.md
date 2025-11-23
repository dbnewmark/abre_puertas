# Guía de Instalación - Sistema de Control de Relés ESP32

## Requisitos Previos

### Servidor Web
- PHP 7.4 o superior
- MariaDB 10.3 o superior
- Servidor web Apache o Nginx
- Extensiones PHP requeridas: PDO, PDO_MySQL, JSON

### Hardware
- Placa ESP32
- Módulo relé compatible con ESP32 (3.3V o 5V)
- Cable USB para programar el ESP32
- Fuente de alimentación

### Software de Desarrollo
- Arduino IDE 1.8.x o superior
- Soporte para placas ESP32 instalado

## Instalación del Backend

### 1. Configurar la Base de Datos

```bash
# Conectar a MariaDB
mysql -u root -p

# Crear la base de datos
CREATE DATABASE abre_puertas CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

# Crear usuario para la aplicación (opcional pero recomendado)
CREATE USER 'abre_puertas_user'@'localhost' IDENTIFIED BY 'tu_password_seguro';
GRANT ALL PRIVILEGES ON abre_puertas.* TO 'abre_puertas_user'@'localhost';
FLUSH PRIVILEGES;

# Salir de MariaDB
EXIT;

# Importar el schema
mysql -u root -p abre_puertas < database/schema.sql
```

### 2. Configurar el Backend PHP

```bash
# Copiar el archivo de configuración de ejemplo
cd backend/config
cp database.example.php database.php

# Editar database.php con tus credenciales
nano database.php
```

Configuración recomendada:
```php
return [
    'host' => 'localhost',
    'database' => 'abre_puertas',
    'username' => 'abre_puertas_user',
    'password' => 'tu_password_seguro',
    'charset' => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',
    'options' => [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]
];
```

### 3. Configurar el Servidor Web

#### Apache

Crear archivo `.htaccess` en la carpeta raíz del proyecto:

```apache
# En la raíz del proyecto
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^backend/(.*)$ backend/$1 [L]
RewriteRule ^frontend/(.*)$ frontend/$1 [L]
```

VirtualHost recomendado:

```apache
<VirtualHost *:80>
    ServerName tudominio.com
    DocumentRoot /var/www/abre_puertas
    
    <Directory /var/www/abre_puertas>
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
    
    ErrorLog ${APACHE_LOG_DIR}/abre_puertas_error.log
    CustomLog ${APACHE_LOG_DIR}/abre_puertas_access.log combined
</VirtualHost>
```

#### Nginx

Configuración recomendada:

```nginx
server {
    listen 80;
    server_name tudominio.com;
    root /var/www/abre_puertas;
    index index.html index.php;

    # Frontend
    location / {
        try_files $uri $uri/ /frontend/$uri /frontend/index.html;
    }

    # Backend API
    location /backend/ {
        try_files $uri $uri/ /backend/$uri;
        
        location ~ \.php$ {
            fastcgi_pass unix:/var/run/php/php7.4-fpm.sock;
            fastcgi_index index.php;
            include fastcgi_params;
            fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        }
    }

    location ~ /\.ht {
        deny all;
    }
}
```

### 4. Configurar el Frontend

Editar `frontend/js/config.js`:

```javascript
// Si el frontend y backend están en el mismo dominio:
const API_BASE_URL = '/backend/api';

// Si están en dominios diferentes (requiere configurar CORS):
// const API_BASE_URL = 'https://api.tudominio.com/backend/api';
```

### 5. Verificar Permisos

```bash
# Asegurar que el servidor web puede leer los archivos
chown -R www-data:www-data /var/www/abre_puertas
chmod -R 755 /var/www/abre_puertas

# Proteger el archivo de configuración
chmod 600 /var/www/abre_puertas/backend/config/database.php
```

## Instalación del Firmware ESP32

### 1. Preparar Arduino IDE

1. Abrir Arduino IDE
2. Ir a **Archivo > Preferencias**
3. En "Gestor de URLs Adicionales de Tarjetas", agregar:
   ```
   https://dl.espressif.com/dl/package_esp32_index.json
   ```
4. Ir a **Herramientas > Placa > Gestor de tarjetas**
5. Buscar "ESP32" e instalar "ESP32 by Espressif Systems"

### 2. Instalar Librerías Requeridas

Ir a **Programa > Incluir Librería > Administrar Bibliotecas** e instalar:
- No se requieren librerías adicionales (se usan las incluidas con ESP32)

### 3. Configurar el Firmware

1. Abrir `firmware/esp32_relay_control.ino`
2. Modificar las credenciales WiFi:
   ```cpp
   const char* ssid = "TU_SSID_WIFI";
   const char* password = "TU_PASSWORD_WIFI";
   ```
3. Modificar la URL del servidor:
   ```cpp
   const char* serverUrl = "http://tudominio.com/backend/api/device_status.php";
   const char* reportUrl = "http://tudominio.com/backend/api/report_status.php";
   ```
4. Verificar el pin del relé:
   ```cpp
   #define RELAY_PIN 2  // Cambiar según tu conexión
   ```

### 4. Cargar el Firmware

1. Conectar el ESP32 al puerto USB
2. En Arduino IDE:
   - **Herramientas > Placa**: Seleccionar tu modelo de ESP32
   - **Herramientas > Puerto**: Seleccionar el puerto COM/USB
   - **Herramientas > Upload Speed**: 115200
3. Hacer clic en el botón "Subir" (→)
4. Esperar a que se complete la carga

### 5. Obtener el Chip ID

1. Abrir el Monitor Serie (115200 baud)
2. Presionar el botón RESET en el ESP32
3. Ver el Chip ID en el monitor:
   ```
   === Sistema de Control de Relé ESP32 ===
   Chip ID: 123456789ABC
   ========================================
   ```
4. Anotar el Chip ID para registrarlo en el frontend

## Conexión del Hardware

### Diagrama de Conexión

```
ESP32                    Módulo Relé
-----                    -----------
GPIO 2 (RELAY_PIN) ----> IN
GND -----------------> GND
3.3V o 5V -----------> VCC

Relé                     Dispositivo
----                     -----------
COM (común) ---------> Fuente de alimentación +
NO (normalmente -------> Dispositivo +
    abierto)
```

### Notas Importantes

- Verificar el voltaje de operación del módulo relé (3.3V o 5V)
- Si el módulo relé requiere 5V, conectar VCC al pin 5V del ESP32
- Los relés pueden manejar altas corrientes y voltajes - tener cuidado
- Nunca conectar cargas de 220V AC directamente sin conocimientos de electricidad

## Primer Uso

### 1. Crear Cuenta de Usuario

1. Acceder a `http://tudominio.com/frontend/register.html`
2. Registrar una cuenta con email y contraseña
3. Iniciar sesión en `http://tudominio.com/frontend/login.html`

### 2. Registrar el ESP32

1. En el dashboard, ingresar el Chip ID del ESP32
2. Opcionalmente, darle un nombre descriptivo
3. Hacer clic en "Registrar"

### 3. Controlar el Relé

1. El dispositivo aparecerá en la lista
2. Usar los botones para controlar el relé:
   - **Encender**: Activa el relé
   - **Apagar**: Desactiva el relé
   - **Alternar**: Cambia el estado actual

### 4. Verificar Funcionamiento

- El ESP32 consulta el servidor cada 5 segundos
- El estado debe actualizarse en tiempo real
- El LED/relé debe cambiar según los comandos

## Solución de Problemas

### El ESP32 no se conecta al WiFi

- Verificar SSID y contraseña
- Asegurar que el WiFi es 2.4GHz (ESP32 no soporta 5GHz)
- Verificar señal WiFi en la ubicación del ESP32

### Error "Dispositivo no registrado"

- Asegurar que el Chip ID está correctamente registrado
- Verificar que la URL del servidor es correcta
- Revisar logs del servidor web

### El relé no responde

- Verificar conexiones de hardware
- Verificar que el pin correcto está configurado
- Probar con el botón local (GPIO 0)

### Error de conexión en el frontend

- Verificar que la URL del API es correcta en `config.js`
- Revisar la consola del navegador para errores
- Verificar que el servidor web está corriendo

### Base de datos no conecta

- Verificar credenciales en `database.php`
- Asegurar que MariaDB está corriendo: `sudo systemctl status mariadb`
- Revisar permisos del usuario de la base de datos

## Seguridad

### Recomendaciones Importantes

1. **HTTPS**: Usar certificados SSL/TLS para conexiones seguras
2. **Contraseñas**: Usar contraseñas fuertes para la base de datos
3. **Firewall**: Configurar firewall para proteger el servidor
4. **Backups**: Realizar backups regulares de la base de datos
5. **Actualizaciones**: Mantener PHP y MariaDB actualizados

### Para Producción

```bash
# Deshabilitar listado de directorios
# En Apache .htaccess:
Options -Indexes

# Proteger archivos sensibles
<FilesMatch "(database\.php|\.git)">
    Require all denied
</FilesMatch>
```

## Soporte

Para problemas o preguntas, revisar:
- Logs del servidor web
- Monitor Serie del ESP32
- Consola del navegador web
