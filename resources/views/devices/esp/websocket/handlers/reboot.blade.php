// Reboot message handler
void handleRebootMessage(const JsonDocument& doc) {
    Serial.println("[MSG] Reboot command received");
    
    // Send confirmation before rebooting
    StaticJsonDocument<200> response;
    response["type"] = "reboot_response";
    response["device_key"] = device_key;
    response["status"] = "success";
    response["message"] = "Rebooting ESP32...";
    String responseMsg;
    serializeJson(response, responseMsg);
    webSocket.sendTXT(responseMsg);
    
    // Wait for the message to be sent
    webSocket.loop();
    delay(100);
    
    // Disconnect cleanly
    webSocket.disconnect();
    WiFi.disconnect();
    
    // Wait a moment before rebooting
    delay(1000);
    
    // Perform the reboot
    ESP.restart();
} 