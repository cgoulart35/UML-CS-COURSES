import RPi.GPIO as GPIO
import time

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
				GPIO.output(23,GPIO.HIGH)
				LED_state = True
				print ('clicked on')
			#if on turn off
			else:
				GPIO.output(23,GPIO.LOW)
				LED_state = False
				print ('clicked off')
				
			time.sleep(1)
			
