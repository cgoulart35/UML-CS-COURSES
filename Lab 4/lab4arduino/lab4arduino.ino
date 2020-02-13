#include <ESP8266WiFi.h>
#include <PubSubClient.h>

// WiFi/MQTT parameters
#define WLAN_SSID       "FILL THIS IN"
#define WLAN_PASS       "FILL THIS IN"
#define BROKER_IP       "FILL THIS IN"

int lightstate;

WiFiClient client;
PubSubClient mqttclient(client);

void setup() {
  Serial.begin(115200);
  
  // connect to wifi
  WiFi.mode(WIFI_STA);
  WiFi.begin(WLAN_SSID, WLAN_PASS);
  while (WiFi.status() != WL_CONNECTED) {
    delay(500);
    Serial.print(F("."));
  }

  Serial.println(F("WiFi connected"));
  Serial.println(F("IP address: "));
  Serial.println(WiFi.localIP());

  // connect to mqtt server
  mqttclient.setServer(BROKER_IP, 1883);
  connect();
}

void loop() {
  if (!mqttclient.connected()) {  //commented this out; for some reasom it kept decreasing button responsiveness & performance.
    connect();                    //maybe it keeps trying to reconnect? No wifi issues...
  }

  //mqttclient.loop();
  
  // put your main code here, to run repeatedly:

  //vars to keep track of time
  static const unsigned long REFRESH_INTERVAL = 1000; // ms
  static unsigned long lastRefreshTime = 0;


  //if time between now and last update is more than time interval
  if(millis() - lastRefreshTime >= REFRESH_INTERVAL)
  {
    lastRefreshTime += REFRESH_INTERVAL;
    lightstate = analogRead(A0);
    Serial.println(lightstate);
    char lightstateStr[4];
    itoa(lightstate, lightstateStr, 10);
    mqttclient.publish("/lightstate", lightstateStr, false);
  }
}

void connect() {
  while (WiFi.status() != WL_CONNECTED) {
    Serial.println(F("Wifi issue"));
    delay(3000);
  }
  Serial.print(F("Connecting to MQTT server... "));
  while(!mqttclient.connected()) {
    if (mqttclient.connect(WiFi.macAddress().c_str())) {
      Serial.println(F("MQTT server Connected!"));
    } else {
      Serial.print(F("MQTT server connection failed! rc="));
      Serial.print(mqttclient.state());
      Serial.println("try again in 10 seconds");
      // Wait 5 seconds before retrying
      delay(20000);
    }
  }
}
