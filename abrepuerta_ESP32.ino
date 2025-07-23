#include <WiFi.h>
#include <WebServer.h>
#include <Preferences.h>
#include <BLEDevice.h>
#include <BLEServer.h>
#include <BLEUtils.h>
#include <BLE2902.h>

// Definiciones
#define WIFI_SETUP_SSID "DoorSetup"
#define WIFI_SETUP_PASS "12345678"
#define SETUP_BUTTON_PIN 0 // GPIO del botón físico para modo setup

Preferences preferences;
WebServer server(80);

String chipID;
String ssid_main, pass_main, ssid_backup, pass_backup;

// BLE variables
BLEServer *pServer = nullptr;
BLECharacteristic *ssidCharacteristic = nullptr;
BLECharacteristic *passCharacteristic = nullptr;
bool bleConnected = false;

// Función para obtener el Chip ID único del ESP32
String getChipID() {
  uint64_t chipid = ESP.getEfuseMac();
  char id[13];
  sprintf(id, "%04X%08X", (uint16_t)(chipid>>32), (uint32_t)chipid);
  return String(id);
}

// Página web de configuración WiFi
void handleRoot() {
  String html = "<html><body>";
  html += "<h2>Configura tu puerta</h2>";
  html += "<form action='/save' method='POST'>";
  html += "SSID principal: <input name='ssid_main'><br>";
  html += "Password principal: <input name='pass_main' type='password'><br>";
  html += "SSID backup: <input name='ssid_backup'><br>";
  html += "Password backup: <input name='pass_backup' type='password'><br>";
  html += "<input type='submit' value='Guardar'>";
  html += "</form>";
  html += "<p>Chip ID: " + chipID + "</p>";
  html += "</body></html>";
  server.send(200, "text/html", html);
}

// Guardar credenciales WiFi desde web
void handleSave() {
  ssid_main = server.arg("ssid_main");
  pass_main = server.arg("pass_main");
  ssid_backup = server.arg("ssid_backup");
  pass_backup = server.arg("pass_backup");

  preferences.begin("wifi", false);
  preferences.putString("ssid_main", ssid_main);
  preferences.putString("pass_main", pass_main);
  preferences.putString("ssid_backup", ssid_backup);
  preferences.putString("pass_backup", pass_backup);
  preferences.end();

  server.send(200, "text/html", "<h2>Datos guardados! Reiniciando...</h2>");
  delay(1000);
  ESP.restart();
}

// Callbacks BLE
class MyServerCallbacks: public BLEServerCallbacks {
  void onConnect(BLEServer* pServer) { bleConnected = true; }
  void onDisconnect(BLEServer* pServer) { bleConnected = false; }
};

// Inicializar BLE para setup y registro
void setupBLE() {
  BLEDevice::init("Puerta_" + chipID);
  pServer = BLEDevice::createServer();
  pServer->setCallbacks(new MyServerCallbacks());

  BLEService *service = pServer->createService(BLEUUID((uint16_t)0x180F));

  // Característica SSID principal
  ssidCharacteristic = service->createCharacteristic(
    BLEUUID((uint16_t)0x2A19),
    BLECharacteristic::PROPERTY_WRITE | BLECharacteristic::PROPERTY_READ
  );
  ssidCharacteristic->setValue("");
  ssidCharacteristic->addDescriptor(new BLE2902());

  // Característica password principal
  passCharacteristic = service->createCharacteristic(
    BLEUUID((uint16_t)0x2A1A),
    BLECharacteristic::PROPERTY_WRITE | BLECharacteristic::PROPERTY_READ
  );
  passCharacteristic->setValue("");
  passCharacteristic->addDescriptor(new BLE2902());

  service->start();
  BLEDevice::startAdvertising();
}

// Leer credenciales BLE y guardar
void checkBLECredentials() {
  if (bleConnected) {
    String ssid = ssidCharacteristic->getValue().c_str();
    String pass = passCharacteristic->getValue().c_str();

    if (ssid.length() > 0 && pass.length() > 0) {
      preferences.begin("wifi", false);
      preferences.putString("ssid_main", ssid);
      preferences.putString("pass_main", pass);
      preferences.end();

      // Responde con chip ID para que la app lo registre en backend
      ssidCharacteristic->setValue(chipID.c_str());
      delay(1000);
      ESP.restart();
    }
  }
}

void setup() {
  pinMode(SETUP_BUTTON_PIN, INPUT_PULLUP);
  chipID = getChipID();

  // Modo setup: WiFi y BLE para alta/configuración
  if (digitalRead(SETUP_BUTTON_PIN) == LOW) {
    WiFi.softAP(WIFI_SETUP_SSID, WIFI_SETUP_PASS);
    server.on("/", handleRoot);
    server.on("/save", HTTP_POST, handleSave);
    server.begin();
    setupBLE();
  } else {
    // Modo normal: conecta a WiFi principal o backup
    preferences.begin("wifi", true);
    ssid_main = preferences.getString("ssid_main", "");
    pass_main = preferences.getString("pass_main", "");
    ssid_backup = preferences.getString("ssid_backup", "");
    pass_backup = preferences.getString("pass_backup", "");
    preferences.end();

    if (ssid_main.length() > 0) {
      WiFi.begin(ssid_main.c_str(), pass_main.c_str());
      int tries = 0;
      while (WiFi.status() != WL_CONNECTED && tries < 20) {
        delay(500);
        tries++;
      }
      if (WiFi.status() != WL_CONNECTED && ssid_backup.length() > 0) {
        WiFi.begin(ssid_backup.c_str(), pass_backup.c_str());
        tries = 0;
        while (WiFi.status() != WL_CONNECTED && tries < 20) {
          delay(500);
          tries++;
        }
      }
    }
    // Aquí puedes agregar tu lógica para conectar con backend, etc.
  }
}

void loop() {
  server.handleClient();
  checkBLECredentials();
  // Tu lógica principal aquí (relé, sensores, buzzer, etc.)
}