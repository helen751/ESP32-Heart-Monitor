#include <SPI.h>
#include <Wire.h>
#include <Adafruit_GFX.h>
#include <Adafruit_SSD1306.h>
#include <WiFi.h>
#include <HTTPClient.h>
#include <WiFiClientSecure.h>
#include <Arduino_JSON.h>

const char* ssid = "SENSOR";
const char* password = "0987654321";

#define SCREEN_WIDTH 128 // OLED display width, in pixels
#define SCREEN_HEIGHT 32 // OLED display height, in pixels

#define OLED_RESET     -1 // Reset pin # (or -1 if sharing Arduino reset pin)
#define SCREEN_ADDRESS 0x3C ///< See datasheet for Address; 0x3D for 128x64, 0x3C for 128x32
Adafruit_SSD1306 display(SCREEN_WIDTH, SCREEN_HEIGHT, &Wire, OLED_RESET);
char* serverName = "https://helencodes.com/iot/post-heart-readings.php";
String apiKeyValue = "fT7gir34KLpuN";
String sensorName = "pulse";
 
 // Define the pulse sensor settings
const int pulsePin = 36; // the pulse sensor pin
const int ledPin = 2; // the LED pin
int pulseValue; // the pulse sensor value
int bpm; // the heart rate in beats per minute


void setup() {
  Serial.begin(9600);
  // Set up the pulse sensor
  pinMode(pulsePin, INPUT);
  pinMode(ledPin, OUTPUT);
  digitalWrite(ledPin, LOW);

  WiFi.begin(ssid, password);
  Serial.println("Connecting");
  while(WiFi.status() != WL_CONNECTED) {
    delay(500);
    Serial.print(".");
  }
  Serial.println("");
  Serial.print("Connected to WiFi network with IP Address: ");
  Serial.println(WiFi.localIP());
Serial.println("started");
  // SSD1306_SWITCHCAPVCC = generate display voltage from 3.3V internally
  if(!display.begin(SSD1306_SWITCHCAPVCC, SCREEN_ADDRESS)) {
    Serial.println(F("SSD1306 allocation failed"));
    for(;;); // Don't proceed, loop forever
  }
  // Clear the buffer
  display.clearDisplay();
  display.display();
  delay(2000);
  testdrawline();  // Draw triangles (filled)

  testdrawstyles();    // Draw 'stylized' characters
}

 
void loop()
{
  // Read the pulse sensor value
  pulseValue = analogRead(pulsePin);

  // Detect the pulse
  if (pulseValue > 550) {
    digitalWrite(ledPin, HIGH); // turn on the LED
    delay(100); // wait for a short time
    digitalWrite(ledPin, LOW); // turn off the LED
    bpm = 60000 / pulseValue*2; // calculate the heart rate in beats per minute
    Serial.print("Heart rate: ");
    Serial.print(bpm);
    Serial.println(" BPM");

    delay(200);

    // Print the heart rate on the serial monitor
    String message = "Heart rate: " + String(bpm) + " BPM";
    Serial.println(message);
  }
   display.clearDisplay();

  display.setTextSize(2);      
  display.setTextColor(SSD1306_WHITE);        // Draw white text
  display.setCursor(2,0);             // Start at top-left corner
  display.print(bpm);
  display.println(" (BPM)");

 
  display.display();
  //Check WiFi connection status
  if(WiFi.status()== WL_CONNECTED){
    WiFiClientSecure *client = new WiFiClientSecure;
    client->setInsecure(); //don't use SSL certificate
    HTTPClient https;
   
    // Your Domain name with URL path or IP address with path
    https.begin(*client, serverName);
   
    // Specify content-type header
    https.addHeader("Content-Type", "application/x-www-form-urlencoded");
   
    // Prepare your HTTP POST request data
    String httpRequestData = "api_key=" + apiKeyValue + "&sensor=" + sensorName
                          + "&value=" + String(bpm) + "";
    Serial.print("httpRequestData: ");
    Serial.println(httpRequestData);
 // Send HTTP POST request
    int httpResponseCode = https.POST(httpRequestData);
     
   
    // If you need an HTTP request with a content type: application/json, use the following:
    //https.addHeader("Content-Type", "application/json");
   
    if (httpResponseCode>0) {
      Serial.print("HTTP Response code: ");
      Serial.println(httpResponseCode);
    }
    else {
      Serial.print("Error code: ");
      Serial.println(httpResponseCode);
    }
    https.end();
  }
  else {
    Serial.println("WiFi Disconnected");
  }
  delay(5000);  
 // Add a small delay to reduce CPU usage
  //delay(20);


}



void testdrawline() {
  int16_t i;

  display.clearDisplay(); // Clear display buffer

  for(i=0; i<display.width(); i+=4) {
    display.drawLine(0, 0, i, display.height()-1, SSD1306_WHITE);
    display.display(); // Update screen with each newly-drawn line
    delay(1);
  }
  for(i=0; i<display.height(); i+=4) {
    display.drawLine(0, 0, display.width()-1, i, SSD1306_WHITE);
    display.display();
    delay(1);
  }
  delay(250);

  display.clearDisplay();

  for(i=0; i<display.width(); i+=4) {
    display.drawLine(0, display.height()-1, i, 0, SSD1306_WHITE);
    display.display();
    delay(1);
  }
  for(i=display.height()-1; i>=0; i-=4) {
    display.drawLine(0, display.height()-1, display.width()-1, i, SSD1306_WHITE);
    display.display();
    delay(1);
  }

 

  delay(2000); // Pause for 2 seconds
}



void testdrawstyles(void) {
  display.clearDisplay();

  display.setTextSize(2);      
  display.setTextColor(SSD1306_WHITE);        // Draw white text
  display.setCursor(0,0);             // Start at top-left corner
  display.println(F("HeartRate Monitor!"));

 
  display.display();
  delay(2000);
}
