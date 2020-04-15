import paho.mqtt.client as mqtt
import RPi.GPIO as GPIO
from influxdb import InfluxDBClient
import datetime
import json
import os
from notify_run import Notify

def on_message(client, userdata, message):

	#get current time
	receiveTime=datetime.datetime.utcnow() - datetime.timedelta(hours=4)
	
	payload = message.payload.decode("utf-8")
	
	#save door open notifications to DB
	if message.topic == "/arduino/door":
		json_body = [
			{
				"measurement": "DOOR",
				"time": receiveTime,
				"fields": {
					"message": payload
				}
			}
		]
		if dbclient.write_points(json_body):
			print("\nWrote:\n", json_body, "\nto InfluxDB database 'SmartMailbox'.\n")
		notify.send('Your smart mailbox door has been opened.')
		os.system('python3 smartMailboxGoogleSpeech.py ' + google_home_mini_address + ' \'Your smart mailbox door has been opened.\'')
		subprocess.Popen(['python3', 'smartMailboxGoogleSpeech.py', google_home_mini_address, '\'Your smart mailbox door has been opened.\''], stdout=subprocess.PIPE)
		
	#save temperature and humidity values to DB
	elif message.topic == "/arduino/dht":
		dht_json = json.loads(payload)
		t = dht_json["temperature"]
		h = dht_json["humidity"]
		json_body = [
			{
				"measurement": "DHT",
				"time": receiveTime,
				"fields": {
					"temperature": t,
					"humidity": h
				}
			}
		]
		if dbclient.write_points(json_body):
			print("\nWrote:\n", json_body, "\nto InfluxDB database 'SmartMailbox'.\n")
	
		
#declare InfluxDB client
dbclient = InfluxDBClient('0.0.0.0', 8086, 'root', 'root', 'SmartMailbox')

#google_home_mini_address
google_home_mini_address = "FILL THIS IN"

#notify.run endpoint
notify = Notify()

#broker address
broker_address="FILL THIS IN"

#create new mqtt client instance
client = mqtt.Client()

#connect to broker
client.connect(broker_address)

#set function to handle subscriptions
client.on_message = on_message

#subscribe to incoming door and dht messages
client.subscribe("/arduino/door")
client.subscribe("/arduino/dht")

#setup board
GPIO.setmode(GPIO.BCM)

#loop endlessly
try:
	while True:
		client.loop()
except KeyboardInterrupt:
	pass

GPIO.cleanup()