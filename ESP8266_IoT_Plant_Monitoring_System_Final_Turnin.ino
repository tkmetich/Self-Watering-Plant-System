#define soilSensor A0
#define waterPump 2
#define ALED 0

#define numLEDS 3

#define DHTPIN 4
#define DHTTYPE DHT20

#include "FastLED.h"
#include "DHT.h"
#include "Wire.h"

#include <Arduino.h>
#include <ArduinoJson.h>
#include <ESP8266WiFi.h>
#include <ESP8266WiFiMulti.h>
#include <ESP8266HTTPClient.h>
#include <WiFiClientSecureBearSSL.h>
// Fingerprint for demo URL, expires on June 2, 2021, needs to be updated well before this date
const uint8_t fingerprint[20] = {0x40, 0xaf, 0x00, 0x6b, 0xec, 0x90, 0x22, 0x41, 0x8e, 0xa3, 0xad, 0xfa, 0x1a, 0xe8, 0x25, 0x41, 0x1d, 0x1a, 0x54, 0xb3};

ESP8266WiFiMulti WiFiMulti;

int lowestMoistureLevel = 840;
int highestMoistureLevel = 380;

CRGB leds[numLEDS];
DHT dht(DHTTYPE);


//-------------------------------------------------------------------------------------------------------------

double getMoisturePercentage( int moisture ) {
  
  if(moisture > lowestMoistureLevel) {
    lowestMoistureLevel = moisture;
  }
  if(moisture < highestMoistureLevel) {
    highestMoistureLevel = moisture;
  }

  int moistureRange = lowestMoistureLevel - highestMoistureLevel;
  int adjustedMoisture = moisture - highestMoistureLevel;

  double moisturePercentage = 100 - (((double)adjustedMoisture / (double)moistureRange)*100);
  return moisturePercentage;
}

//-------------------------------------------------------------------------------------------------------------

void checkWatering( double moisturePercentage ) {
  if( moisturePercentage < 25.0 ) { 
    digitalWrite(waterPump, HIGH);
    delay(500);
    digitalWrite(waterPump, LOW);
    delay(10000);
  }
}

//-------------------------------------------------------------------------------------------------------------

void updateLeds( double moisturePercentage, double humidity, double temp ) {
  int wHue = 100 - (abs(60 - moisturePercentage)/60) * 100;
  leds[0].setHue(wHue); 

  int hHue = 100 - (abs(40 - humidity)/40) * 100;
  leds[1].setHue(hHue); 

  int tHue = 100 - (abs(70 - temp)/70) * 100;
  leds[2].setHue(tHue); 

  Serial.println("changing LEDS");
  
  FastLED.show();
}

//============================================================================================================

void setup() {
  Serial.begin(115200);
  pinMode(waterPump, OUTPUT);
  FastLED.addLeds<WS2812B, ALED, GRB>(leds, numLEDS);
  FastLED.setBrightness(10);

  Wire.begin();
  dht.begin();

  WiFi.mode(WIFI_STA);
  WiFiMulti.addAP("BW_Jacket"); 
}

//-------------------------------------------------------------------------------------------------------------

void loop() {

  delay(3000);

  int moisture = analogRead(soilSensor); // Reads soil moisture level
  double humidity = dht.readHumidity();
  double temp = dht.readTemperature(true);

  double moisturePercentage = getMoisturePercentage(moisture);
  checkWatering(moisturePercentage);
  updateLeds(moisturePercentage, humidity, temp);

  Serial.print("Humidity: ");
  Serial.println(humidity);

  Serial.print("Temperature: ");
  Serial.println(temp);

  Serial.print("Moisture percent: ");
  Serial.println(moisturePercentage);



  // wait for WiFi connection
  if ((WiFiMulti.run() == WL_CONNECTED)) {

    WiFiClient client;
    HTTPClient https;

    Serial.print("[HTTPS] begin...\n");
    if (https.begin(client, "http://10.19.24.190/readings.php")) {  // HTTPS
      DynamicJsonDocument doc(1024);
      doc["temp"] = temp;
      doc["humidity"]=humidity; // NEW CODE
      doc["moisture"]=moisturePercentage; // NEW CODE

      char serialized[1024];
      serializeJson(doc, serialized);
      Serial.printf("Here is our serialized version: %s", serialized);

      Serial.print("[HTTPS] GET...\n");
      // start connection and send HTTP header
      int httpCode = https.POST(serialized);

      // httpCode will be negative on error
      if (httpCode > 0) {
        // HTTP header has been send and Server response header has been handled
        Serial.printf("[HTTPS] GET... code: %d\n", httpCode);

        // file found at server
        if (httpCode == HTTP_CODE_OK || httpCode == HTTP_CODE_MOVED_PERMANENTLY) {
          String payload = https.getString();
          
          deserializeJson(doc, payload);

          const char * status = doc["status"];
          Serial.printf("Status is %s\n", status);
          
          Serial.println(payload);
        }
      } else {
        Serial.printf("[HTTPS] GET... failed, error: %s\n", https.errorToString(httpCode).c_str());
      }

      https.end();
    } else {
      Serial.printf("[HTTPS] Unable to connect\n");
    }
  }

  Serial.println("Wait 10s before next round...");

}
