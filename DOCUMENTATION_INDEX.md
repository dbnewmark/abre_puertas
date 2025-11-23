# 📚 Guía de Documentación - Sistema de Control de Relés ESP32

Bienvenido al sistema de control de relés ESP32. Esta guía te ayudará a navegar por toda la documentación disponible.

## 🗂️ Índice de Documentación

### 1. 📖 [README.md](README.md) - **EMPEZAR AQUÍ**
**Para:** Todos los usuarios  
**Duración:** 5 minutos  
**Contenido:**
- Descripción general del proyecto
- Características principales
- Estructura del proyecto
- Tecnologías utilizadas
- Licencia

**👉 Lee esto primero para entender qué hace el sistema.**

---

### 2. 🚀 [QUICKSTART.md](QUICKSTART.md) - **Instalación Rápida**
**Para:** Administradores, instaladores  
**Duración:** 30-60 minutos  
**Contenido:**
- Guía paso a paso para poner en marcha el sistema
- Configuración del servidor (5 min)
- Configuración del backend (3 min)
- Programación del ESP32 (15 min)
- Registro y prueba (5 min)
- Solución rápida de problemas

**👉 Sigue esta guía si quieres tener el sistema funcionando lo más rápido posible.**

---

### 3. 📋 [INSTALLATION.md](INSTALLATION.md) - **Instalación Detallada**
**Para:** Administradores, desarrolladores  
**Duración:** 1-2 horas  
**Contenido:**
- Requisitos previos detallados
- Instalación completa del backend
- Configuración de MariaDB
- Configuración de Apache/Nginx
- Instalación del frontend
- Instalación del firmware ESP32
- Conexión del hardware
- Procedimientos de primer uso
- Solución de problemas exhaustiva
- Recomendaciones de seguridad

**👉 Usa esta guía para una instalación completa y profesional, especialmente para entornos de producción.**

---

### 4. 🔌 [API_DOCUMENTATION.md](API_DOCUMENTATION.md) - **Referencia de la API**
**Para:** Desarrolladores  
**Duración:** Referencia  
**Contenido:**
- Descripción de todos los endpoints
- Formato de requests y responses
- Códigos de error
- Ejemplos con cURL
- Estructura de datos
- Autenticación
- Seguridad

**👉 Consulta este documento cuando necesites integrar con la API o entender cómo funciona cada endpoint.**

---

### 5. 📊 [PROJECT_SUMMARY.md](PROJECT_SUMMARY.md) - **Resumen Técnico**
**Para:** Desarrolladores, gerentes técnicos  
**Duración:** 15 minutos  
**Contenido:**
- Resumen ejecutivo del proyecto
- Componentes del sistema
- Estructura de archivos
- Flujo de funcionamiento
- Esquema de base de datos
- Características principales
- Métricas del sistema
- Posibles mejoras futuras
- Notas para desarrolladores

**👉 Lee esto para obtener una visión completa y técnica del sistema en poco tiempo.**

---

### 6. 🏗️ [ARCHITECTURE.md](ARCHITECTURE.md) - **Arquitectura del Sistema**
**Para:** Desarrolladores, arquitectos  
**Duración:** 20 minutos  
**Contenido:**
- Diagramas de arquitectura (ASCII art)
- Vista general del sistema
- Flujo de datos detallado
- Flujo de autenticación
- Flujo de registro de ESP32
- Arquitectura de seguridad
- Escalabilidad
- Stack tecnológico

**👉 Revisa este documento para entender cómo interactúan todos los componentes del sistema.**

---

## 🎯 ¿Qué Documento Necesito?

### Si eres...

#### 👤 **Usuario Final**
1. Lee el [README.md](README.md) para entender qué hace el sistema
2. Tu administrador ya debería tener todo configurado
3. Solo necesitas:
   - Crear una cuenta
   - Registrar tu ESP32
   - Controlar el relé desde el dashboard

#### 🔧 **Instalador/Administrador**
1. **Instalación rápida**: [QUICKSTART.md](QUICKSTART.md)
2. **Instalación profesional**: [INSTALLATION.md](INSTALLATION.md)
3. Si tienes problemas: Sección de troubleshooting en INSTALLATION.md

#### 💻 **Desarrollador Frontend**
1. [README.md](README.md) - Descripción general
2. [API_DOCUMENTATION.md](API_DOCUMENTATION.md) - Endpoints y formato
3. [ARCHITECTURE.md](ARCHITECTURE.md) - Flujos de datos
4. Archivos en `/frontend` como referencia

#### ⚙️ **Desarrollador Backend**
1. [PROJECT_SUMMARY.md](PROJECT_SUMMARY.md) - Resumen técnico
2. [ARCHITECTURE.md](ARCHITECTURE.md) - Arquitectura completa
3. [API_DOCUMENTATION.md](API_DOCUMENTATION.md) - Endpoints
4. Archivos en `/backend` como referencia

#### 🔌 **Desarrollador de Hardware/ESP32**
1. [README.md](README.md) - Descripción general
2. [QUICKSTART.md](QUICKSTART.md) - Sección ESP32
3. [API_DOCUMENTATION.md](API_DOCUMENTATION.md) - Endpoints ESP32
4. Archivo `/firmware/esp32_relay_control.ino` como referencia

#### 👔 **Gerente/Tomador de Decisiones**
1. [README.md](README.md) - ¿Qué hace?
2. [PROJECT_SUMMARY.md](PROJECT_SUMMARY.md) - Resumen ejecutivo
3. Sección "Métricas" en PROJECT_SUMMARY.md

---

## 🔍 Búsqueda Rápida por Tema

### Instalación
- Instalación rápida → [QUICKSTART.md](QUICKSTART.md)
- Instalación detallada → [INSTALLATION.md](INSTALLATION.md)
- Requisitos del servidor → [INSTALLATION.md](INSTALLATION.md#requisitos-previos)

### Configuración
- Configurar base de datos → [INSTALLATION.md](INSTALLATION.md#1-configurar-la-base-de-datos)
- Configurar backend PHP → [QUICKSTART.md](QUICKSTART.md#2-configurar-el-backend-php)
- Configurar ESP32 → [QUICKSTART.md](QUICKSTART.md#2-programar-el-esp32)

### API
- Lista de endpoints → [API_DOCUMENTATION.md](API_DOCUMENTATION.md)
- Autenticación → [API_DOCUMENTATION.md](API_DOCUMENTATION.md#autenticación)
- Ejemplos de uso → [API_DOCUMENTATION.md](API_DOCUMENTATION.md#ejemplos-de-uso)

### Arquitectura
- Diagrama general → [ARCHITECTURE.md](ARCHITECTURE.md#vista-general-del-sistema)
- Flujo de control → [ARCHITECTURE.md](ARCHITECTURE.md#flujo-de-datos---control-de-relé)
- Base de datos → [PROJECT_SUMMARY.md](PROJECT_SUMMARY.md#esquema-de-base-de-datos)

### Seguridad
- Prácticas de seguridad → [INSTALLATION.md](INSTALLATION.md#seguridad)
- Arquitectura de seguridad → [ARCHITECTURE.md](ARCHITECTURE.md#arquitectura-de-seguridad)
- Configuración HTTPS → [INSTALLATION.md](INSTALLATION.md#para-producción)

### Troubleshooting
- Problemas comunes → [QUICKSTART.md](QUICKSTART.md#solución-rápida-de-problemas)
- Solución detallada → [INSTALLATION.md](INSTALLATION.md#solución-de-problemas)

### Desarrollo
- Agregar endpoint → [PROJECT_SUMMARY.md](PROJECT_SUMMARY.md#notas-para-desarrolladores)
- Modificar base de datos → [PROJECT_SUMMARY.md](PROJECT_SUMMARY.md#notas-para-desarrolladores)
- Estructura de archivos → [PROJECT_SUMMARY.md](PROJECT_SUMMARY.md#estructura-de-archivos)

---

## 📁 Archivos de Código Importantes

### Backend
```
backend/
├── api/
│   ├── common.php              ← Utilidades, CORS, autenticación helper
│   ├── register.php            ← Registro de usuarios
│   ├── login.php               ← Login
│   ├── control_relay.php       ← ⭐ Controlar relé
│   ├── device_status.php       ← ⭐ Estado para ESP32
│   └── ...
└── config/
    ├── Database.php            ← Conexión DB
    └── Auth.php                ← Sistema de autenticación
```

### Frontend
```
frontend/
├── index.html                  ← ⭐ Dashboard principal
├── login.html                  ← Login
├── test.html                   ← Test de conectividad
├── js/
│   ├── dashboard.js            ← ⭐ Lógica del dashboard
│   └── config.js               ← Configuración API URL
└── css/
    └── style.css               ← Estilos completos
```

### Firmware
```
firmware/
└── esp32_relay_control.ino     ← ⭐ Código para ESP32
```

### Base de Datos
```
database/
└── schema.sql                  ← ⭐ Schema completo de MariaDB
```

---

## 🆘 ¿Necesitas Ayuda?

### Problema de Instalación
1. Revisa [QUICKSTART.md](QUICKSTART.md#solución-rápida-de-problemas)
2. Si no lo resuelve, ve a [INSTALLATION.md](INSTALLATION.md#solución-de-problemas)
3. Usa `frontend/test.html` para diagnosticar

### Problema con la API
1. Consulta [API_DOCUMENTATION.md](API_DOCUMENTATION.md)
2. Verifica formato de request/response
3. Revisa códigos de error

### No Entiendo Cómo Funciona
1. Lee [ARCHITECTURE.md](ARCHITECTURE.md) - Diagramas visuales
2. Revisa [PROJECT_SUMMARY.md](PROJECT_SUMMARY.md#flujo-de-funcionamiento)

### Quiero Modificar el Sistema
1. Lee [PROJECT_SUMMARY.md](PROJECT_SUMMARY.md#notas-para-desarrolladores)
2. Estudia [ARCHITECTURE.md](ARCHITECTURE.md) para entender la estructura
3. Revisa el código fuente relevante

---

## 📝 Orden de Lectura Recomendado

### Para Instalación Completa
1. [README.md](README.md) - 5 min
2. [QUICKSTART.md](QUICKSTART.md) o [INSTALLATION.md](INSTALLATION.md) - 30-120 min
3. [API_DOCUMENTATION.md](API_DOCUMENTATION.md) - Referencia según necesites

### Para Desarrollo
1. [README.md](README.md) - 5 min
2. [PROJECT_SUMMARY.md](PROJECT_SUMMARY.md) - 15 min
3. [ARCHITECTURE.md](ARCHITECTURE.md) - 20 min
4. [API_DOCUMENTATION.md](API_DOCUMENTATION.md) - Referencia
5. Código fuente

### Para Entender el Sistema
1. [README.md](README.md) - 5 min
2. [ARCHITECTURE.md](ARCHITECTURE.md) - 20 min
3. [PROJECT_SUMMARY.md](PROJECT_SUMMARY.md) - 15 min

---

## 🎓 Recursos Adicionales

### Archivos de Ejemplo
- `backend/config/database.example.php` - Plantilla de configuración DB
- `frontend/test.html` - Herramienta de diagnóstico

### Dentro del Código
- Comentarios en `backend/config/Auth.php` - Lógica de autenticación
- Comentarios en `firmware/esp32_relay_control.ino` - Configuración ESP32
- Comentarios en `database/schema.sql` - Estructura de tablas

---

## 💡 Tips

- **Primera vez**: Empieza con QUICKSTART.md
- **Producción**: Lee INSTALLATION.md completamente
- **Desarrollo**: Ten API_DOCUMENTATION.md a mano
- **Debug**: Usa frontend/test.html
- **Comprensión**: Lee ARCHITECTURE.md con los diagramas

---

## ✅ Checklist de Lectura

Marca lo que has leído:

- [ ] README.md - Descripción general
- [ ] QUICKSTART.md o INSTALLATION.md - Instalación
- [ ] API_DOCUMENTATION.md - Referencia API (si desarrollas)
- [ ] PROJECT_SUMMARY.md - Resumen técnico
- [ ] ARCHITECTURE.md - Arquitectura y diagramas
- [ ] Este archivo (DOCUMENTATION_INDEX.md)

---

**¡Buena suerte con tu proyecto de control de relés ESP32!** 🚀🔌
