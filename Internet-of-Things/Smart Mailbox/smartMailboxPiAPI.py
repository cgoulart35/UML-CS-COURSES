import paho.mqtt.client as mqtt
from influxdb import InfluxDBClient
from flask import Flask, request, json
from flask_restful import Resource, Api
import datetime
import subprocess
import time

broker_address = "FILL THIS IN"
google_home_mini_address = "FILL THIS IN"

client = mqtt.Client()
client.connect(broker_address)

dbclient = InfluxDBClient('0.0.0.0', 8086, 'root', 'root', 'SmartMailbox')

app = Flask(__name__)
api = Api(app)

class SmartMailboxControl(Resource):
	def post(self):
		value = request.get_data()
		value = json.loads(value)
		if value['action'] == 'lock':
			if value['state'] == 'lock':
				client.publish("/arduino/lock","lock")
				return {'status': 'success'}
			elif value['state'] == 'unlock':
				client.publish("/arduino/lock","unlock")
				return {'status': 'success'}
		elif value['action'] == 'flag':
			if value['state'] == 'up':
				client.publish("/arduino/flag","up")
				return {'status': 'success'}
			elif value['state'] == 'down':
				client.publish("/arduino/flag","down")
				return {'status': 'success'}
		elif value['action'] == 'get_dht':
			client.publish("/arduino/get_dht")
			return {'status': 'success'}
		elif value['action'] == 'dance':
			subprocess.Popen(['python3', 'smartMailboxGoogleSpeech.py', google_home_mini_address, 'dance'], stdout=subprocess.PIPE)
			time.sleep(5.5)
			for i in range(0,16):
				client.publish("/arduino/flag","down")
				time.sleep(0.4035)
				client.publish("/arduino/flag","up")
				time.sleep(0.4035)
			for i in range(0,32):
				client.publish("/arduino/flag","down")
				time.sleep(0.261)
				client.publish("/arduino/flag","up")
				time.sleep(0.261)
			return {'status': 'success'}
		else:
			return {'status': 'failed: provide action and state'}
			
class SmartMailboxDataDoor(Resource):
	def get(self):
		query = 'SELECT time, message FROM DOOR ORDER BY time DESC LIMIT 5'
		result = dbclient.query(query)
		times_opened_list = list(result.get_points(measurement='DOOR'))
		if times_opened_list:
			message = 'The mailbox was last opened at '
			return_object = {}
			try:
				most_recent = times_opened_list[0]['time']
				message += most_recent
				return_object.update({'most recent': most_recent})
			except IndexError:
				pass
			try:
				second_recent = times_opened_list[1]['time']
				message += ', and ' + second_recent
				return_object.update({'second recent': second_recent})
			except IndexError:
				pass
			try:
				third_recent = times_opened_list[2]['time']
				message += ', and ' + third_recent
				return_object.update({'third recent': third_recent})
			except IndexError:
				pass
			try:
				fourth_recent = times_opened_list[3]['time']
				message += ', and ' + fourth_recent
				return_object.update({'fourth recent': fourth_recent})
			except IndexError:
				pass
			try:
				fifth_recent = times_opened_list[4]['time']
				message += ', and ' + fifth_recent
				return_object.update({'fifth recent': fifth_recent})
			except IndexError:
				pass
			subprocess.Popen(['python3', 'smartMailboxGoogleSpeech.py', google_home_mini_address, '\'' + message + '\''], stdout=subprocess.PIPE)
			return return_object
		else:
			subprocess.Popen(['python3', 'smartMailboxGoogleSpeech.py', google_home_mini_address, '\'There are no times recorded in the database\''], stdout=subprocess.PIPE)
			return {'status': 'no recorded data'}
			
class SmartMailboxDataDHT(Resource):
	def get(self):
		query = 'SELECT LAST(temperature), humidity FROM DHT'
		result = dbclient.query(query)
		dht_list = list(result.get_points(measurement='DHT'))
		if dht_list:
			last_temperature = dht_list[0]['last']
			last_humidity = dht_list[0]['humidity']
			p1 = subprocess.Popen(['python3', 'smartMailboxGoogleSpeech.py', google_home_mini_address, '\'' + str(last_temperature) + 'degrees celcius and' + str(last_humidity) + 'percent\''], stdout=subprocess.PIPE)
			p1.wait()
			subprocess.Popen(['python3', 'smartMailboxGoogleSpeech.py', google_home_mini_address, 'thatsHot'], stdout=subprocess.PIPE)
			return {'temperature': last_temperature, 'humidity': last_humidity}
		else:
			subprocess.Popen(['python3', 'smartMailboxGoogleSpeech.py', google_home_mini_address, '\'There are no temperature and humidity values recorded in the database\''], stdout=subprocess.PIPE)
			return {'status': 'no recorded data'}
		
api.add_resource(SmartMailboxControl, '/SmartMailbox/Control')
api.add_resource(SmartMailboxDataDoor, '/SmartMailbox/Data/Door')
api.add_resource(SmartMailboxDataDHT, '/SmartMailbox/Data/DHT')

app.run(host='0.0.0.0', debug=True)