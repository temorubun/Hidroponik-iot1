// Connection management variables
bool isConnected = false;
bool isAuthenticated = false;
unsigned long lastWifiReconnectAttempt = 0;
unsigned long lastWebSocketReconnectAttempt = 0;
bool wasConnected = false;
unsigned long lastAuthAttempt = 0;
const unsigned long AUTH_RETRY_INTERVAL = 3000; // 3 seconds

void checkConnection() {
    unsigned long currentMillis = millis();
    
    // Check WiFi connection
    if (WiFi.status() != WL_CONNECTED) {
        if (currentMillis - lastWifiReconnectAttempt >= WIFI_RECONNECT_INTERVAL) {
            Serial.println("[WIFI] Connection lost, reconnecting...");
            WiFi.disconnect();
            WiFi.begin(ssid, password);
            lastWifiReconnectAttempt = currentMillis;
            isConnected = false;
            isAuthenticated = false;
        }
        return;
    }
    
    // If WiFi is connected but WebSocket isn't
    if (WiFi.status() == WL_CONNECTED && !isConnected) {
        if (currentMillis - lastWebSocketReconnectAttempt >= WEBSOCKET_RECONNECT_INTERVAL) {
            Serial.println("[WS] Attempting to reconnect...");
            webSocket.disconnect();
            delay(100); // Give time for clean disconnect
            webSocket.begin(ws_host, ws_port, ws_path);
            lastWebSocketReconnectAttempt = currentMillis;
            isAuthenticated = false;
        }
        return;
    }

    // If connected but not authenticated
    if (isConnected && !isAuthenticated) {
        if (currentMillis - lastAuthAttempt >= AUTH_RETRY_INTERVAL) {
            Serial.println("[WS] Not authenticated, sending auth request...");
            authenticate();
            lastAuthAttempt = currentMillis;
        }
    }

    // Track connection state changes
    if (isConnected != wasConnected) {
        wasConnected = isConnected;
        if (isConnected) {
            Serial.println("[WS] Connection established");
            // Reset authentication state on new connection
            isAuthenticated = false;
            lastAuthAttempt = currentMillis;
            authenticate();
        } else {
            Serial.println("[WS] Connection lost");
            isAuthenticated = false;
        }
    }
}

// WebSocket Connection Manager
void setupWebSocket() {
    webSocket.begin(ws_host, ws_port, ws_path);
    webSocket.onEvent(webSocketEvent);
    webSocket.setReconnectInterval(5000);
    
    // Initial authentication will be handled by checkConnection
    isConnected = false;
    isAuthenticated = false;
}

void authenticate() {
    if (!isConnected) {
        Serial.println("[WS] Cannot authenticate: not connected");
        return;
    }
    
    StaticJsonDocument<200> doc;
    doc["type"] = "auth";
    doc["device_key"] = device_key;
    
    String output;
    serializeJson(doc, output);
    
    Serial.println("[WS] Sending authentication request...");
    webSocket.sendTXT(output);
}

// Call this when authentication is successful
void onAuthenticationSuccess() {
    isAuthenticated = true;
    Serial.println("[WS] Authentication successful!");
    
    // Request pin configurations
    StaticJsonDocument<200> request;
    request["type"] = "get_pins";
    request["device_key"] = device_key;
    String requestStr;
    serializeJson(request, requestStr);
    Serial.println("[WS] Requesting pin configurations...");
    webSocket.sendTXT(requestStr);
}

void handleWebSocketConnection() {
    webSocket.loop();
    
    // Check if connection lost
    if (!webSocket.isConnected()) {
        static unsigned long lastReconnectAttempt = 0;
        unsigned long currentMillis = millis();
        
        // Try to reconnect every 5 seconds
        if (currentMillis - lastReconnectAttempt > 5000) {
            lastReconnectAttempt = currentMillis;
            setupWebSocket();
        }
    }
} 