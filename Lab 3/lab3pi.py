import paho.mqtt.client as mqtt
import RPi.GPIO as GPIO
import time


def on_message(client, userdata, message):
	message.payload = message.payload.decode("utf-8")
	print(message.topic, message.payload)
	if message.topic == "/led/pi":
		if message.payload == "on":
			GPIO.output(23,GPIO.HIGH)
		if message.payload == "off":
			GPIO.output(23,GPIO.LOW)


broker_address="FILL THIS IN" #broker address (your pis ip address)

client = mqtt.Client() #create new mqtt client instance

client.connect(broker_address) #connect to broker

client.subscribe("/led/pi")

client.on_message=on_message


#setup board
GPIO.setmode(GPIO.BCM)

#setup pin as input
GPIO.setup(18, GPIO.IN, pull_up_down=GPIO.PUD_UP)

#setup pin as output
GPIO.setup(23, GPIO.OUT)


#var
clickedflag = False

#LED state
LED_state = False

#loop endlessly
while True:

		client.loop_start() #start client

		#get button state
		buttonstate = GPIO.input(18)

		#if the button is pressed and was not previously clicked
		if (buttonstate and not clickedflag):
			#set as clicked
			clickedflag = True;

		#if button is not clicked and was previously not clicked 
		if (not buttonstate and clickedflag):
			#set as not clicked
			clickedflag = False;

			#button was clicked
			
			#if off turn on
			if(LED_state == False):
				client.publish("/led/arduino","on")
				LED_state = True
				print ('clicked on')
			#if on turn off
			else:
				client.publish("/led/arduino","off")
				LED_state = False
				print ('clicked off')
				
			time.sleep(1)
			
			client.loop_stop()
