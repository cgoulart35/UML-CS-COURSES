// Libraries
#include <ESP8266WiFi.h>
#include <PubSubClient.h>
#include <Servo.h> 
#include <DHT.h>

// WiFi/MQTT parameters
#define WLAN_SSID       "FILL THIS IN"
#define WLAN_PASS       "FILL THIS IN"
#define BROKER_IP       "FILL THIS IN"

// Used pins
#define LOCK 5
#define DOOR 9
#define FLAG 4
#define DHTPIN 13
#define BUTTON 12

// Declare sensor variables, states, and time counter
bool lockState = false;
int doorState; //1 for open, 0 for closed
bool notificationSent = false;
Servo flagServo;
bool flagUp = true;
bool buttonstate;
bool completeClick = false;
DHT dht(DHTPIN, DHT11);
int timeSent = 0;

// WiFi/MQTT clients
WiFiClient client;
PubSubClient mqttclient(client);

// Function called when received message of subscribed topic
void callback (char* topic, byte* payload, unsigned int length) {
  Serial.write(payload, length);

  // add null terminator to byte payload so we can treat it as a string
  payload[length] = '\0'; 

  // locking and unlocking the door
  if (strcmp(topic, "/arduino/lock") == 0){
     Serial.print("\n");
     if (strcmp((char *)payload, "lock") == 0){
        lockState = true;
        digitalWrite(LOCK, HIGH);
        delay(1500);  // delay further computation to help prevent false misreadings on magnetic door sensor
     } else if (strcmp((char *)payload, "unlock") == 0){
        lockState = false;
        digitalWrite(LOCK, LOW);
        delay(1500);  // delay further computation to help prevent false misreadings on magnetic door sensor
     }
  }
  // raising and lower the flag
  else if (strcmp(topic, "/arduino/flag") == 0) {
     Serial.print("\n");
     if (strcmp((char *)payload, "up") == 0){
        flagServo.write(90); 
        flagUp = true;
     } else if (strcmp((char *)payload, "down") == 0){
        flagServo.write(0); 
        flagUp = false;
     }
  }
  // requesting an on-demand temperature and humidity reading
  else if (strcmp(topic, "/arduino/get_dht") == 0) {
     Serial.print("get_dht\n");
     sendDHTdata();
  }
}

// Setup the ESP8266 with this function
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

  // setup the sensors
  pinMode(LOCK, OUTPUT);
  pinMode(DOOR, INPUT_PULLUP);
  pinMode(BUTTON, INPUT);
  flagServo.attach(FLAG);
  dht.begin();
}

// Loop function that handles incoming and outgoing MQTT messages
void loop() {
  if (!mqttclient.connected()) {
    connect();
  }

  // send MQTT message once every time the door is opened
  // check to see if the door is unlocked, because locking the door actuator may cause accidental false misreadings of magnetic sensor
  doorState = digitalRead(DOOR);
  if (doorState == HIGH && notificationSent == false) {// && lockState == false){
    //send notification that door opened
    Serial.print("door opened\n");
    mqttclient.publish("/arduino/door", "Door opened.");
    delay(200);
    notificationSent = true;
  }
  else if(doorState == LOW) {
    notificationSent = false;
  }

  // check to see if button was pushed to lower/raise flag manually
  buttonstate = digitalRead(BUTTON);
  if (buttonstate and not completeClick){
    completeClick = true;
  }
  if (not buttonstate and completeClick){
    completeClick = false;
    if(flagUp) {
      flagServo.write(0); 
      flagUp = false;
    }
    else {
      flagServo.write(90); 
      flagUp = true;
    }
  }

  // send the temperature and humidity every 5 minutes
  if (millis() - timeSent > 300000) {
    Serial.print("send_dht\n");
    sendDHTdata();
    timeSent = millis();
  }
  
  mqttclient.loop();
}

// Function to read temperature and humidity and publish data to broker (used more than once)
void sendDHTdata() {
  
  String h = String(dht.readHumidity());
  String t = String(dht.readTemperature());

  String message = "{\"humidity\": " + h + ", \"temperature\": " + t + "}";
  char messageArr[message.length()+1];
  message.toCharArray(messageArr, message.length()+1);
  messageArr[message.length()+1] = '\0';
  
  mqttclient.publish("/arduino/dht", messageArr);
}

// Connect to MQTT function
void connect() {
  while (WiFi.status() != WL_CONNECTED) {
    Serial.println(F("Wifi issue"));
    delay(3000);
  }
  Serial.print(F("Connecting to MQTT server... "));
  while(!mqttclient.connected()) {
    if (mqttclient.connect(WiFi.macAddress().c_str())) {
      Serial.println(F("MQTT server Connected!"));
      //subscribed to topics for the door lock and the flag
      mqttclient.subscribe("/arduino/flag");
      mqttclient.subscribe("/arduino/lock");
      mqttclient.subscribe("/arduino/get_dht");
    } else {
      Serial.print(F("MQTT server connection failed! rc="));
      Serial.print(mqttclient.state());
      Serial.println("try again in 10 seconds");
      // Wait 5 seconds before retrying
      delay(20000);
    }
  }
}
