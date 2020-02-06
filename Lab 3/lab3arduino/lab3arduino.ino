#include <ESP8266WiFi.h>
#include <PubSubClient.h>

// WiFi/MQTT parameters
#define WLAN_SSID       "FILL THIS IN"
#define WLAN_PASS       "FILL THIS IN"
#define BROKER_IP       "FILL THIS IN"

//pins
#define LED 5 //define 
#define BUTTON 4

bool buttonstate;
bool LED_state = false;
bool clickedflag = false;

WiFiClient client;
PubSubClient mqttclient(client);

void callback (char* topic, byte* payload, unsigned int length) {
  Serial.println(topic);
  Serial.write(payload, length); //print incoming messages
  Serial.println("");

  payload[length] = '\0'; // add null terminator to byte payload so we can treat it as a string

  if (strcmp(topic, "/led/arduino") == 0){
     if (strcmp((char *)payload, "on") == 0){
        digitalWrite(LED, HIGH);
     } else if (strcmp((char *)payload, "off") == 0){
        digitalWrite(LED, LOW);
     }
  }
}

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
  mqttclient.setCallback(callback);
  connect();

  //setup pins
  pinMode(LED, OUTPUT); // setup pin for input
  pinMode(BUTTON, INPUT);
}

void loop() {
  //if (!mqttclient.connected()) {  //commented this out; for some reasom it kept decreasing button responsiveness & performance.
  //  connect();                    //maybe it keeps trying to reconnect? No wifi issues...
  //}

  mqttclient.loop();
  
  // put your main code here, to run repeatedly:

  // read button state
  buttonstate = digitalRead(BUTTON);

  // if the button is clicked and it was not clicked previously
  if (buttonstate and not clickedflag){
      
      // set the clicked flag
      clickedflag = true;
  }

  // if the button is not clicked now and it was clicked previously (a complete click has now happened)
  if (not buttonstate and clickedflag){

    // set the clicked flag
    clickedflag = false;
      
      //if off turn on
      if(LED_state == false) {
        mqttclient.publish("/led/pi","on");
        LED_state = true;
        Serial.println("clicked on");
      }
      //if on turn off
      else {
        mqttclient.publish("/led/pi","off");
        LED_state = false;
        Serial.println("clicked off");
      }       
      delay(1000);
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

       mqttclient.subscribe("/led/arduino");
      
    } else {
      Serial.print(F("MQTT server connection failed! rc="));
      Serial.print(mqttclient.state());
      Serial.println("try again in 10 seconds");
      // Wait 5 seconds before retrying
      delay(20000);
    }
  }
}
