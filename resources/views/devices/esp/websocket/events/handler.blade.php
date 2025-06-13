// WebSocket event handler
void webSocketEvent(WStype_t type, uint8_t * payload, size_t length) {
    switch(type) {
        case WStype_CONNECTED:
            Serial.println("[WS] Connected to server");
            Serial.printf("[WS] Server URL: %s:%d\n", ws_host, ws_port);
            Serial.printf("[WS] Device Key: %s\n", device_key);
            isConnected = true;
            // Authentication will be handled by checkConnection
            break;
            
        case WStype_TEXT:
            Serial.printf("[WS] Received text message (length: %d): ", length);
            if (payload) {
                Serial.println((char*)payload);
                
                // Parse message
                StaticJsonDocument<512> doc;
                DeserializationError error = deserializeJson(doc, payload);
                if (!error) {
                    const char* type = doc["type"] | "unknown";
                    Serial.printf("[WS] Message type: %s\n", type);
                    
                    if (strcmp(type, "auth_response") == 0) {
                        const char* status = doc["status"] | "unknown";
                        const char* message = doc["message"] | "";
                        Serial.printf("[WS] Auth response - status: %s, message: %s\n", status, message);
                        
                        if (strcmp(status, "success") == 0) {
                            onAuthenticationSuccess();
                        } else {
                            Serial.println("[WS] Authentication failed!");
                            isAuthenticated = false;
                            webSocket.disconnect();
                        }
                    } else if (strcmp(type, "config") == 0 || strcmp(type, "pin_config") == 0) {
                        if (isAuthenticated) {
                            Serial.println("[WS] Received pin configuration");
                            if (doc["settings"].isNull()) {
                                handleConfigMessage(doc);
                            } else {
                                // Handle pin_config message with settings
                                const char* pinType = doc["settings"]["type"] | "unknown";
                                int pin = doc["pin"] | -1;
                                
                                if (strcmp(pinType, "ph_sensor") == 0) {
                                    int samples = doc["settings"]["samples"] | 10;
                                    int interval = doc["settings"]["interval"] | 1000;
                                    
                                    if (addPhSensor(pin, samples, interval)) {
                                        if (!doc["settings"]["calibration"].isNull()) {
                                            float cal4 = doc["settings"]["calibration"]["4"] | 4090.0f;
                                            float cal7 = doc["settings"]["calibration"]["7"] | 3140.0f;
                                            float cal10 = doc["settings"]["calibration"]["10"] | 2350.0f;
                                            updatePhCalibration(pin, cal4, cal7, cal10);
                                        }
                                        
                                        // Send confirmation
                                        StaticJsonDocument<200> response;
                                        response["type"] = "pin_configured";
                                        response["pin"] = pin;
                                        response["pin_type"] = pinType;
                                        response["status"] = "success";
                                        String responseMsg;
                                        serializeJson(response, responseMsg);
                                        webSocket.sendTXT(responseMsg);
                                    }
                                } else {
                                    handleConfigMessage(doc);
                                }
                            }
                        } else {
                            Serial.println("[WS] Ignoring config message - not authenticated");
                        }
                    } else {
                        if (isAuthenticated) {
                            handleWebSocketMessage(payload);
                        } else {
                            Serial.println("[WS] Ignoring message - not authenticated");
                        }
                    }
                } else {
                    Serial.printf("[WS] Failed to parse message: %s\n", error.c_str());
                }
            } else {
                Serial.println("[WS] Error: Empty payload");
            }
            break;
            
        case WStype_DISCONNECTED:
            Serial.println("[WS] Disconnected from server");
            Serial.println("[WS] Will attempt to reconnect...");
            isConnected = false;
            isAuthenticated = false;
            break;
            
        case WStype_ERROR:
            Serial.printf("[WS] Error occurred: %s\n", payload ? (char*)payload : "Unknown error");
            isConnected = false;
            isAuthenticated = false;
            break;
            
        case WStype_PING:
            Serial.println("[WS] Received ping");
            // Send pong response with device key
            if (isAuthenticated) {
                StaticJsonDocument<200> response;
                response["type"] = "pong";
                response["device_key"] = device_key;
                response["timestamp"] = millis();
                String responseMsg;
                serializeJson(response, responseMsg);
                webSocket.sendTXT(responseMsg);
                Serial.println("[WS] Sent pong response with device key");
            }
            break;
            
        case WStype_PONG:
            Serial.println("[WS] Received pong");
            break;
    }
} 