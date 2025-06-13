// WebSocket message handler
void handleWebSocketMessage(uint8_t *payload) {
    StaticJsonDocument<512> doc;
    DeserializationError error = deserializeJson(doc, payload);
    
    if (error) {
        Serial.println("[MSG] Failed to parse message");
        return;
    }
    
    const char* type = doc["type"] | "unknown";
    Serial.printf("[MSG] Received message type: %s\n", type);
    
    if (strcmp(type, "pin") == 0) {
        handlePinMessage(doc);
    }
    else if (strcmp(type, "config") == 0) {
        handleConfigMessage(doc);
    }
    else if (strcmp(type, "pin_config") == 0) {
        handlePinConfigMessage(doc);
    }
    else if (strcmp(type, "cleanup_pin") == 0) {
        handleCleanupMessage(doc);
    }
    else if (strcmp(type, "reboot") == 0) {
        handleRebootMessage(doc);
    }
} 