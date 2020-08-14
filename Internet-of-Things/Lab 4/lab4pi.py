import paho.mqtt.client as mqtt
import RPi.GPIO as GPIO

from influxdb import InfluxDBClient
import datetime

def on_message(client, userdata, message):
	#get current time
	receiveTime=datetime.datetime.utcnow()
	payload = int(message.payload.decode("utf-8"))
	if message.topic == "/lightstate":
		#save ALL lightstate values to influxdb
		#create json to insert into db
		json_body = [
			{
				"measurement": '/lightstate',
				"time": receiveTime,
				"fields": {
					"value": payload
				}
			}
		]
		#write to db
		dbclient.write_points(json_body)
		print("Wrote:   ", message.topic, payload, "   to InfluxDB database 'mydb'.")
		
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

#setup board
GPIO.setmode(GPIO.BCM)

#setup pin as output
GPIO.setup(23, GPIO.OUT)

#loop endlessly
while True:

	#start client
	client.loop_start()
	
	#query db for average light value from past 10 secs
	query = 'select mean("value") from "/lightstate" where "time" > now() - 10s'
	result = dbclient.query(query)
	try:
		light_avg = list(result.get_points(measurement='/lightstate'))[0]['mean']
		print("			Current average: ", light_avg)
		if light_avg <= 200:
			GPIO.output(23,GPIO.HIGH)
		else:
			GPIO.output(23,GPIO.LOW)
	except:
		print('exception')
		pass
	
	client.loop_stop()