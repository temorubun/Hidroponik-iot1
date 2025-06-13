// Pin message handler
void handlePinMessage(const JsonDocument& doc) {
    int pin = doc["pin"] | -1;
    int value = doc["value"] | -1;
    
    Serial.printf("[MSG] Pin control message - Pin: %d, Value: %d\n", pin, value);
    
    if (pin >= 0 && value >= 0) {
        if (pins[pin].isConfigured) {
            setPinValue(pin, value);
            
            // Send confirmation back
            StaticJsonDocument<200> response;
            response["type"] = "pin_update";
            response["pin"] = pin;
            response["value"] = value;
            response["status"] = "success";
            String responseMsg;
            serializeJson(response, responseMsg);
            Serial.printf("[MSG] Sending pin update confirmation: %s\n", responseMsg.c_str());
            webSocket.sendTXT(responseMsg);
        } else {
            Serial.printf("[MSG] Error: Pin %d is not configured\n", pin);
            
            // Send error back
            StaticJsonDocument<200> response;
            response["type"] = "error";
            response["message"] = "Pin not configured";
            response["pin"] = pin;
            String responseMsg;
            serializeJson(response, responseMsg);
            Serial.printf("[MSG] Sending error response: %s\n", responseMsg.c_str());
            webSocket.sendTXT(responseMsg);
        }
    } else {
        Serial.println("[MSG] Invalid pin or value in message");
    }
} 