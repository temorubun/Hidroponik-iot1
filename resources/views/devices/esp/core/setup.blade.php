void setup() {
    Serial.begin(115200);
    Serial.println("\n[INIT] Starting device...");
    Serial.printf("[INIT] Device Key: %s\n", device_key);
    
    // Initialize WiFi
    WiFi.mode(WIFI_STA);
    WiFi.setAutoReconnect(true);
    WiFi.persistent(true);
    
    // Connect to WiFi
    Serial.printf("[WIFI] Connecting to WiFi: %s\n", ssid);
    WiFi.begin(ssid, password);
    
    // Wait for WiFi connection
    int attempts = 0;
    while (WiFi.status() != WL_CONNECTED && attempts < 20) {
        delay(500);
        Serial.print(".");
        attempts++;
    }
    
    if (WiFi.status() == WL_CONNECTED) {
        Serial.println("\n[WIFI] Connected successfully");
        Serial.printf("[WIFI] IP Address: %s\n", WiFi.localIP().toString().c_str());
    } else {
        Serial.println("\n[WIFI] Connection failed, will retry in loop");
    }
    
    // Initialize WebSocket
    Serial.printf("[WS] Connecting to WebSocket server: %s:%d\n", ws_host, ws_port);
    webSocket.begin(ws_host, ws_port, "/");
    webSocket.onEvent(webSocketEvent);
    webSocket.setReconnectInterval(5000);
    
    // Reset semua pin
    Serial.println("[INIT] Resetting all pins...");
    for (int i = 0; i < 40; i++) {
        pins[i].isConfigured = false;
    }
    
    // Konfigurasi pin default
    Serial.println("[INIT] Setting up default pins...");
    @foreach($device->pins as $pin)
    setupPin({{ $pin->pin_number }}, {{ $pin->type == 'digital_output' ? 'OUTPUT' : 'INPUT' }}, "{{ $pin->type }}");
    @endforeach
    Serial.println("[INIT] Setup complete!");
} 