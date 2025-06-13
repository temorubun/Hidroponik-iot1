// Cleanup message handler
void handleCleanupMessage(const JsonDocument& doc) {
    int pin = doc["pin"] | -1;
    
    if (pin >= 0) {
        Serial.printf("[MSG] Received cleanup request for pin %d\n", pin);
        cleanupPin(pin);
        
        // Send confirmation back
        StaticJsonDocument<200> response;
        response["type"] = "pin_cleaned";
        response["pin"] = pin;
        response["status"] = "success";
        String responseMsg;
        serializeJson(response, responseMsg);
        Serial.printf("[MSG] Sending cleanup confirmation: %s\n", responseMsg.c_str());
        webSocket.sendTXT(responseMsg);
    } else {
        Serial.println("[MSG] Invalid pin in cleanup message");
    }
} 