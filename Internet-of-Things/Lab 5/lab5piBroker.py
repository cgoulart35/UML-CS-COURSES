import paho.mqtt.client as mqtt
import RPi.GPIO as GPIO

from influxdb import InfluxDBClient
import datetime

def on_message(client, userdata, message):
	#get current time
	receiveTime=datetime.datetime.utcnow()
	payload = message.payload.decode("utf-8")
	if message.topic == "/lightstate":
		#save ALL lightstate values to influxdb
		#create json to insert into db
		json_body = [
			{
				"measurement": '/lightstate',
				"time": receiveTime,
				"fields": {
					"value": int(payload)
				}
			}
		]
		#write to db
		dbclient.write_points(json_body)
		print("Wrote:   ", message.topic, int(payload), "   to InfluxDB database 'mydb'.")
	if message.topic == "/led/pi":
		if payload == "on":
			GPIO.output(23,GPIO.HIGH)
		if payload == "off":
			GPIO.output(23,GPIO.LOW)
		
# Set up a client for InfluxDB
dbclient = InfluxDBClient('0.0.0.0', 8086, 'root', 'root', 'mydb')
		
#broker address (your pis ip address)
broker_address="FILL THIS IN"

#create new mqtt client instance
client = mqtt.Client()

#connect to broker
client.connect(broker_address)

client.on_message=on_message

client.subscribe("/lightstate")
client.subscribe("/led/pi")

#setup board
GPIO.setmode(GPIO.BCM)

#setup pin as output
GPIO.setup(23, GPIO.OUT)

client.loop_start()

#loop endlessly
try:
	while True:
		pass #wait for ctrl-c
except KeyboardInterrupt:
	pass

client.loop_stop()
GPIO.cleanup()