#include <WiFi.h>          
#include <PubSubClient.h>

WiFiClient espClient;

PubSubClient client(espClient);

const char* ssid = "ssid"; // Wifi SSID
const char* password = "password"; // Wifi Password

const char* server = "192.168.43.115";  
const char* username = "accesskey_username"; //change with your key username from access key page
const char* secret = "accesskey_secret"; //change with your key secret from access key page
const char* ClientID = "Client123"; //Keep this client ID
//if you want to change it, dont forget to change also in the send_topic and get_topic

//route for publish (send) data
const char* send_topic = "master/Client123/writeattributevalue/attribute_name/Project_id"; //change attribute_name and Project_id with your attribute and Project ID
//route for subscribe (receive) data
const char* get_topic = "master/Client123/attribute/attribute_name/Project_id"; //change attribute_name and Project_id with your attribute and Project ID

void setup() {
  Serial.begin(115200);
  Serial.println(ssid);
  WiFi.begin(ssid, password);

  while (WiFi.status() != WL_CONNECTED) {
    delay(500);
  }
  Serial.println(WiFi.localIP());
  client.setServer(server, 1883);
  client.setCallback(callback);
}

void loop() {
  if (!client.connected()) {
    reconnect();
  }
  client.loop();
  //Publish number format:
  client.publish(send_topic, "99");

  delay(10000); //every 10 second send the value
}

//MQTT callback
void callback(char* get_topic, byte* payload, unsigned int length) {
  Serial.print(get_topic);
  Serial.print(" has sent: ");
  char message[length + 1];
  for (int i = 0; i < length; i++) {
    message[i] = (char)payload[i];
  }
  message[length] = '\0';
  Serial.println(message);
}

//MQTT reconnect
void reconnect() {
  // Loop until we're reconnected
  while (!client.connected()) {
    Serial.print("********** Attempting MQTT connection...");
    if (client.connect(ClientID, username, secret)) {
      Serial.println("-> MQTT client connected");
      client.subscribe(get_topic);
      Serial.print("Subscribed to: ");
      Serial.println(get_topic);
    } else {
      Serial.print("failed, rc=");
      Serial.print(client.state());
      Serial.println("-> try again in 5 seconds");
      delay(5000);
    }
  }
}
