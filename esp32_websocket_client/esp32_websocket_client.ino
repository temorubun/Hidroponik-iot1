#include <WiFi.h>
#include <WebSocketsClient.h>
#include <ArduinoJson.h>

const char* ssid = "YOUR_WIFI_SSID";
const char* password = "YOUR_WIFI_PASSWORD";
const char* websocket_server = "your_server_ip";
const int websocket_port = 6001;

WebSocketsClient webSocket;

void webSocketEvent(WStype_t type, uint8_t * payload, size_t length) {
    switch(type) {
        case WStype_DISCONNECTED:
            Serial.println("Disconnected!");
            break;
        case WStype_CONNECTED:
            Serial.println("Connected!");
            break;
        case WStype_TEXT:
            Serial.printf("Received text: %s\n", payload);
            handleMessage((char*)payload);
            break;
    }
}

void handleMessage(char* payload) {
    StaticJsonDocument<200> doc;
    DeserializationError error = deserializeJson(doc, payload);
    
    if (error) {
        Serial.print("deserializeJson() failed: ");
        Serial.println(error.c_str());
        return;
    }
    
    const char* type = doc["type"];
    if (strcmp(type, "command") == 0) {
        // Handle command
        const char* action = doc["action"];
        Serial.print("Received command: ");
        Serial.println(action);
    }
}

void setup() {
    Serial.begin(115200);
    
    // Connect to WiFi
    WiFi.begin(ssid, password);
    while (WiFi.status() != WL_CONNECTED) {
        delay(500);
        Serial.print(".");
    }
    Serial.println("Connected to WiFi");
    
    // Configure WebSocket client
    webSocket.begin(websocket_server, websocket_port, "/");
    webSocket.onEvent(webSocketEvent);
    webSocket.setReconnectInterval(5000);
}

void loop() {
    webSocket.loop();
    
    // Send sensor data every 5 seconds
    static unsigned long lastSendTime = 0;
    if (millis() - lastSendTime > 5000) {
        // Read sensor data
        float temperature = random(20, 30); // Replace with actual sensor reading
        float humidity = random(40, 80);    // Replace with actual sensor reading
        
        // Create JSON document
        StaticJsonDocument<200> doc;
        doc["type"] = "sensor_data";
        doc["temperature"] = temperature;
        doc["humidity"] = humidity;
        
        // Serialize JSON to string
        String jsonString;
        serializeJson(doc, jsonString);
        
        // Send data
        webSocket.sendTXT(jsonString);
        lastSendTime = millis();
    }
} 