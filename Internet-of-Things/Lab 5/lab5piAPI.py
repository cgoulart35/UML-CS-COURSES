import paho.mqtt.client as mqtt
from influxdb import InfluxDBClient
from flask import Flask, request, json
from flask_restful import Resource, Api
import datetime

broker_address = "FILL THIS IN"

client = mqtt.Client()
client.connect(broker_address)

dbclient = InfluxDBClient('0.0.0.0', 8086, 'root', 'root', 'mydb')

app = Flask(__name__)
api = Api(app)

class Test(Resource):
	def get(self):
		query = 'select mean("value") from "/lightstate" where "time" > now() - 10s'
		result = dbclient.query(query)
		try:
			light_avg = list(result.get_points(measurement='/lightstate'))[0]['mean']
			return {'average':light_avg}
		except:
			print('exception')
			pass
	def post(self):
		value = request.get_data()
		value = json.loads(value)
		if value['device'] == 'pi':
			if value['state'] == 'on':
				client.publish("/led/pi","on")
			elif value['state'] == 'off':
				client.publish("/led/pi","off")
		elif value['device'] == 'arduino':
			if value['state'] == 'on':
				client.publish("/led/arduino","on")
			elif value['state'] == 'off':
				client.publish("/led/arduino","off")
		
api.add_resource(Test, '/test')

app.run(host='0.0.0.0', debug=True)