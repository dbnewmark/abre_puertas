#include <WiFi.h>
#include <HTTPClient.h>

// --- Configuración WiFi ---
// IMPORTANTE: Cambiar estas credenciales antes de cargar el firmware
const char* ssid = "TU_SSID_WIFI";
const char* password = "TU_PASSWORD_WIFI";

// --- Configuración del servidor ---
// IMPORTANTE: Cambiar a tu dominio. En producción usar HTTPS para mayor seguridad
// Ejemplo HTTPS: const char* serverUrl = "https://tudominio.com/backend/api/device_status.php";
const char* serverUrl = "http://tudominio.com/backend/api/device_status.php";
const char* reportUrl = "http://tudominio.com/backend/api/report_status.php";

// --- Pines del ESP32 ---
#define RELAY_PIN 2        // Pin GPIO para controlar el relé
#define LED_PIN 2          // LED integrado (mismo pin que relé - cambiarlo si se usa un pin diferente)
#define SETUP_BUTTON_PIN 0 // Pin del botón de setup (GPIO 0 - BOOT button)

// --- Variables globales ---
String chipId;
int currentRelayState = 0;
unsigned long lastCheck = 0;
const unsigned long CHECK_INTERVAL = 5000; // Consultar cada 5 segundos

// --- Función para obtener el Chip ID único del ESP32 ---
String getChipID() {
  uint64_t chipid = ESP.getEfuseMac();
  char id[17];
  sprintf(id, "%04X%08X", (uint16_t)(chipid>>32), (uint32_t)chipid);
  return String(id);
}

// --- Conectar a WiFi ---
void connectWiFi() {
  Serial.println("Conectando a WiFi...");
  WiFi.begin(ssid, password);
  
  int attempts = 0;
  while (WiFi.status() != WL_CONNECTED && attempts < 20) {
    delay(500);
    Serial.print(".");
    attempts++;
  }
  
  if (WiFi.status() == WL_CONNECTED) {
    Serial.println("\nWiFi conectado!");
    Serial.print("IP: ");
    Serial.println(WiFi.localIP());
    Serial.print("Chip ID: ");
    Serial.println(chipId);
  } else {
    Serial.println("\nError al conectar WiFi");
  }
}

// --- Controlar relé ---
void setRelayState(int state) {
  currentRelayState = state;
  digitalWrite(RELAY_PIN, state);
  Serial.print("Relé: ");
  Serial.println(state ? "ENCENDIDO" : "APAGADO");
}

// --- Consultar estado del relé desde el servidor ---
void checkRelayStatus() {
  if (WiFi.status() != WL_CONNECTED) {
    Serial.println("WiFi desconectado, reconectando...");
    connectWiFi();
    return;
  }
  
  HTTPClient http;
  String url = String(serverUrl) + "?chip_id=" + chipId;
  
  http.begin(url);
  int httpCode = http.GET();
  
  if (httpCode > 0) {
    String payload = http.getString();
    Serial.println("Respuesta del servidor: " + payload);
    
    // Parsear respuesta simple JSON
    // Formato esperado: {"success":true,"relay_state":1,"device_id":123}
    if (payload.indexOf("\"success\":true") > 0) {
      // Buscar relay_state
      int statePos = payload.indexOf("\"relay_state\":");
      if (statePos > 0) {
        int valueStart = statePos + 14;
        int newState = payload.substring(valueStart, valueStart + 1).toInt();
        
        // Solo actualizar si cambió
        if (newState != currentRelayState) {
          setRelayState(newState);
          reportStatus(); // Reportar cambio
        }
      }
    } else if (payload.indexOf("\"error\":\"Dispositivo no registrado\"") > 0) {
      Serial.println("ERROR: Dispositivo no registrado en el servidor");
      Serial.println("Por favor registra este Chip ID en el frontend: " + chipId);
    }
  } else {
    Serial.print("Error HTTP: ");
    Serial.println(httpCode);
  }
  
  http.end();
}

// --- Reportar estado actual al servidor ---
void reportStatus() {
  if (WiFi.status() != WL_CONNECTED) return;
  
  HTTPClient http;
  http.begin(reportUrl);
  http.addHeader("Content-Type", "application/json");
  
  String jsonData = "{\"chip_id\":\"" + chipId + "\",\"relay_state\":" + String(currentRelayState) + "}";
  
  int httpCode = http.POST(jsonData);
  
  if (httpCode > 0) {
    String payload = http.getString();
    Serial.println("Estado reportado: " + payload);
  }
  
  http.end();
}

void setup() {
  Serial.begin(115200);
  delay(1000);
  
  // Configurar pines
  pinMode(RELAY_PIN, OUTPUT);
  pinMode(LED_PIN, OUTPUT);
  pinMode(SETUP_BUTTON_PIN, INPUT_PULLUP);
  
  // Estado inicial del relé (apagado)
  setRelayState(0);
  
  // Obtener Chip ID
  chipId = getChipID();
  Serial.println("\n=== Sistema de Control de Relé ESP32 ===");
  Serial.print("Chip ID: ");
  Serial.println(chipId);
  Serial.println("========================================\n");
  
  // Conectar a WiFi
  connectWiFi();
  
  // Primera consulta
  if (WiFi.status() == WL_CONNECTED) {
    checkRelayStatus();
  }
}

void loop() {
  // Consultar estado cada CHECK_INTERVAL
  if (millis() - lastCheck >= CHECK_INTERVAL) {
    lastCheck = millis();
    checkRelayStatus();
  }
  
  // Botón de prueba local (opcional)
  if (digitalRead(SETUP_BUTTON_PIN) == LOW) {
    delay(50); // Debounce
    if (digitalRead(SETUP_BUTTON_PIN) == LOW) {
      // Alternar relé manualmente
      setRelayState(!currentRelayState);
      reportStatus();
      
      // Esperar a que se suelte el botón
      while (digitalRead(SETUP_BUTTON_PIN) == LOW) {
        delay(10);
      }
    }
  }
  
  delay(10);
}
