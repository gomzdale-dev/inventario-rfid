#include <WiFi.h>
#include <HTTPClient.h>
#include <SPI.h>
#include <MFRC522.h>

#define SS_PIN 5
#define RST_PIN 21

MFRC522 mfrc522(SS_PIN, RST_PIN);

// WIFI DE LA CASA
const char* WIFI_SSID = "NOMBRE_DE_LA_RED";
const char* WIFI_PASSWORD = "CONTRASENA_DE_LA_RED";

// API LOCAL DE LARAVEL
const char* API_URL = "http://IP_DE_LA_COMPUTADORA:8001/api/rfid-scan";

// MISMA CLAVE CONFIGURADA EN EL .env
const char* DEVICE_KEY = "ITCA_RFID_2026_CAMBIAR_ESTA_CLAVE";

const char* LECTOR = "ESP32-RC522-01";
const char* LABORATORIO = "Salon C-201";

String ultimoUid = "";
unsigned long ultimaLectura = 0;
const unsigned long TIEMPO_BLOQUEO = 3000;

void conectarWiFi() {
  WiFi.mode(WIFI_STA);
  WiFi.begin(WIFI_SSID, WIFI_PASSWORD);

  Serial.print("Conectando al WiFi");
  unsigned long inicio = millis();

  while (WiFi.status() != WL_CONNECTED) {
    delay(500);
    Serial.print(".");

    if (millis() - inicio > 20000) {
      Serial.println();
      Serial.println("No fue posible conectar al WiFi. Reintentando...");
      WiFi.disconnect();
      delay(1000);
      WiFi.begin(WIFI_SSID, WIFI_PASSWORD);
      inicio = millis();
    }
  }

  Serial.println();
  Serial.println("WiFi conectado.");
  Serial.print("IP del ESP32: ");
  Serial.println(WiFi.localIP());
  Serial.print("Servidor Laravel: ");
  Serial.println(API_URL);
}

void iniciarLectorRfid() {
  Serial.println();
  Serial.println("Inicializando lector RC522...");

  SPI.begin(18, 19, 23, SS_PIN);
  delay(200);

  mfrc522.PCD_Init();
  delay(300);

  mfrc522.PCD_AntennaOn();
  mfrc522.PCD_SetAntennaGain(mfrc522.RxGain_max);

  byte version = mfrc522.PCD_ReadRegister(mfrc522.VersionReg);

  Serial.print("Version detectada del RC522: 0x");
  Serial.println(version, HEX);

  if (version == 0x00 || version == 0xFF) {
    Serial.println("ERROR: El ESP32 no se comunica con el RC522.");
    Serial.println("Revisar SDA, SCK, MOSI, MISO, RST, GND y 3.3V.");
  } else {
    Serial.println("RC522 conectado correctamente.");
  }
}

bool enviarUidLaravel(const String& uid) {
  if (WiFi.status() != WL_CONNECTED) {
    conectarWiFi();
  }

  WiFiClient client;
  HTTPClient http;

  client.setTimeout(8000);

  if (!http.begin(client, API_URL)) {
    Serial.println("No se pudo iniciar la conexion HTTP.");
    return false;
  }

  http.setConnectTimeout(8000);
  http.setTimeout(8000);

  http.addHeader("Content-Type", "application/json");
  http.addHeader("Accept", "application/json");
  http.addHeader("X-Device-Key", DEVICE_KEY);

  String json = "{";
  json += "\"codigo_rfid\":\"" + uid + "\",";
  json += "\"lector\":\"" + String(LECTOR) + "\",";
  json += "\"laboratorio\":\"" + String(LABORATORIO) + "\",";
  json += "\"evento\":\"lectura\"";
  json += "}";

  Serial.print("Enviando UID a Laravel: ");
  Serial.println(uid);

  int codigoHttp = http.POST(json);

  if (codigoHttp < 0) {
    Serial.print("Detalle del error HTTP: ");
    Serial.println(http.errorToString(codigoHttp).c_str());
  }

  String respuesta = http.getString();

  Serial.print("Codigo HTTP: ");
  Serial.println(codigoHttp);

  Serial.print("Respuesta Laravel: ");
  Serial.println(respuesta);

  http.end();

  return codigoHttp == 201;
}

void setup() {
  Serial.begin(115200);
  delay(1500);

  Serial.println();
  Serial.println("==================================");
  Serial.println("LECTOR RFID CONECTADO A LARAVEL");
  Serial.println("==================================");

  conectarWiFi();
  iniciarLectorRfid();

  Serial.println();
  Serial.println("Acerque la tarjeta o el llavero RFID.");
}

void loop() {
  if (!mfrc522.PICC_IsNewCardPresent()) {
    delay(50);
    return;
  }

  if (!mfrc522.PICC_ReadCardSerial()) {
    delay(50);
    return;
  }

  String uid = "";

  for (byte i = 0; i < mfrc522.uid.size; i++) {
    if (mfrc522.uid.uidByte[i] < 0x10) {
      uid += "0";
    }

    uid += String(mfrc522.uid.uidByte[i], HEX);
  }

  uid.toUpperCase();

  bool repetida =
    uid == ultimoUid &&
    millis() - ultimaLectura < TIEMPO_BLOQUEO;

  if (!repetida) {
    Serial.println();
    Serial.println("Tarjeta detectada.");
    Serial.print("UID: ");
    Serial.println(uid);

    bool registrado = enviarUidLaravel(uid);

    if (registrado) {
      Serial.println("Lectura registrada correctamente en Laravel.");
    } else {
      Serial.println("Laravel no pudo registrar la lectura.");
    }

    Serial.println("----------------------------------");

    ultimoUid = uid;
    ultimaLectura = millis();
  }

  mfrc522.PICC_HaltA();
  mfrc522.PCD_StopCrypto1();

  delay(500);
}