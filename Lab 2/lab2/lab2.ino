// vars
#define LED 5
#define BUTTON 4
bool buttonstate;
bool LED_state = false;
bool clickedflag = false;

void setup() {
  // put your setup code here, to run once:

  // setup serial connunication speed
  Serial.begin(9600);

  // setup pin as input
  pinMode(BUTTON, INPUT);

  // setup pin as output
  pinMode(LED, OUTPUT);
}

void loop() {
  // put your main code here, to run repeatedly:

  // read button state
  buttonstate = digitalRead(BUTTON);

  // if the button is clicked and it was not clicked previously
  if (buttonstate and not clickedflag){
      
      // set the clicked flag
      clickedflag = true;
  }

  // if the button is not clicked now and it was clicked previously (a commplete click has now happened)
  if (not buttonstate and clickedflag){

    // set the clicked flag
    clickedflag = false;
      
      //if off turn on
      if(LED_state == false) {
        digitalWrite(LED, HIGH);
        LED_state = true;
        Serial.println("clicked on");
      }
      //if on turn off
      else {
        digitalWrite(LED, LOW);
        LED_state = false;
        Serial.println("clicked off");
      }       
      delay(1000);
  }
  
}
