// Analog pin functions
void handleAnalogPins() {
    unsigned long currentMillis = millis();
    
    for(int i = 0; i < 40; i++) {
        if(pins[i].isConfigured && pins[i].type == "analog_input") {
            if(currentMillis - analogPins[i].lastRead >= analogPins[i].readInterval) {
                analogPins[i].lastRead = currentMillis;
                
                // Read analog value using the actual pin number
                int pinNumber = pins[i].number;
                int value = analogRead(pinNumber);
                Serial.printf("[ANALOG] Pin %d value: %d\n", pinNumber, value);
                
                // Send to WebSocket
                StaticJsonDocument<200> doc;
                doc["type"] = "sensor_data";
                doc["pin"] = pinNumber;
                doc["value"] = value;
                doc["raw_adc"] = value;
                doc["timestamp"] = currentMillis;
                
                String message;
                serializeJson(doc, message);
                webSocket.sendTXT(message);
            }
        }
    }
} 