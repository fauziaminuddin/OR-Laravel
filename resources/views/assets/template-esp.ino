
const char* server = "localhost";  
const char* username = "accesskey_username"; //change with your key username from access key page
const char* secret = "accesskey_secret"; //change with your key secret from access key page
const char* ClientID = "yourclient";

//route for publish data
const char* send_topic = "master/yourclient/writeattributevalue/{attribute_name}/{assetId}";
//route for subscribe data
const char* get_topic = "master/yourclient/attribute/{attribute_name}/{assetId}";

//change the {attribute_name} with your attribute name

void setup() {
    //your setup code
    client.setServer(server, 1883);
}

