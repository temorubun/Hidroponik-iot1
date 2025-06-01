@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-code"></i> IoT Code for {{ $device->name }}
                        </h5>
                        <div>
                            <button class="btn btn-light btn-sm" onclick="copyDeviceCode()">
                                <i class="fas fa-copy"></i> Copy Code
                            </button>
                            <a href="{{ route('devices.show', $device) }}" class="btn btn-light btn-sm ms-2">
                                <i class="fas fa-arrow-left"></i> Back
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <pre><code id="deviceCode">// ESP32 Code for {{ $device->name }}
#include &lt;WiFi.h&gt;
#include &lt;WebSocketsClient.h&gt;
#include &lt;ArduinoJson.h&gt;
#include &lt;map&gt;  // Add map header

// WiFi Configuration
const char* ssid = "{{ $device->wifi_ssid ?: 'YOUR_WIFI_SSID' }}";
const char* password = "{{ $device->wifi_password ?: 'YOUR_WIFI_PASSWORD' }}";

// WebSocket Configuration
const char* ws_host = "{{ request()->getHost() }}";
const uint16_t ws_port = {{ config('websocket.port', 6001) }};
const char* device_key = "{{ $device->device_key }}";

WebSocketsClient webSocket;

// Pin structure
struct Pin {
    int number;
    int mode;
    int value;
    bool isConfigured;
    String type;  // Added type field
};

// Array of pins
Pin pins[40];

// Tambahkan variabel global untuk status koneksi
bool isConnected = false;
unsigned long lastReconnectAttempt = 0;
const unsigned long reconnectInterval = 5000; // 5 detik

// pH Sensor handling
struct PHSensor {
    int pin;
    int samples;
    int interval;
    float calibration[3][2] = {
        {4.01, 0},  // pH 4.01 calibration point
        {6.86, 0},  // pH 6.86 calibration point
        {9.18, 0}   // pH 9.18 calibration point
    };
    unsigned long lastRead = 0;
    float lastValue = 0;
    bool isConfigured = false;
};

// Maximum number of pH sensors we can handle
const int MAX_PH_SENSORS = 8;
PHSensor phSensors[MAX_PH_SENSORS];
int numPhSensors = 0;

// Function to find pH sensor by pin number
int findPhSensor(int pin) {
    for(int i = 0; i < numPhSensors; i++) {
        if(phSensors[i].pin == pin) {
            return i;
        }
    }
    return -1;
}

// Function to add or update pH sensor
bool addPhSensor(int pin, int samples, int interval) {
    if(numPhSensors >= MAX_PH_SENSORS) {
        return false;
    }
    
    int index = findPhSensor(pin);
    if(index == -1) {
        // Add new sensor
        index = numPhSensors++;
    }
    
    phSensors[index].pin = pin;
    phSensors[index].samples = samples;
    phSensors[index].interval = interval;
    phSensors[index].isConfigured = true;
    phSensors[index].lastRead = 0;
    
    Serial.printf("[pH] Configured sensor on pin %d with %d samples and %d ms interval\n", 
        pin, samples, interval);
    
    return true;
}

// Function to update pH sensor calibration
void updatePhCalibration(int pin, float cal4, float cal7, float cal10) {
    int index = findPhSensor(pin);
    if(index >= 0) {
        Serial.printf("\n[pH] Updating calibration for pin %d\n", pin);
        Serial.printf("[pH] Previous calibration values:\n");
        Serial.printf("  pH 4.01: ADC = %.1f\n", phSensors[index].calibration[0][1]);
        Serial.printf("  pH 6.86: ADC = %.1f\n", phSensors[index].calibration[1][1]);
        Serial.printf("  pH 9.18: ADC = %.1f\n", phSensors[index].calibration[2][1]);
        
        phSensors[index].calibration[0][0] = 4.01;
        phSensors[index].calibration[0][1] = cal4;
        
        phSensors[index].calibration[1][0] = 6.86;
        phSensors[index].calibration[1][1] = cal7;
        
        phSensors[index].calibration[2][0] = 9.18;
        phSensors[index].calibration[2][1] = cal10;
        
        Serial.printf("[pH] New calibration values for pin %d:\n", pin);
        Serial.printf("  pH 4.01: ADC = %.1f\n", cal4);
        Serial.printf("  pH 6.86: ADC = %.1f\n", cal7);
        Serial.printf("  pH 9.18: ADC = %.1f\n", cal10);
    } else {
        Serial.printf("[pH] Error: Cannot update calibration, sensor not found on pin %d\n", pin);
    }
}

// Function to read pH value
float readPH(PHSensor &sensor) {
    long sum = 0;
    int validSamples = 0;
    Serial.printf("[pH] Starting pH reading on pin %d\n", sensor.pin);
    Serial.printf("[pH] Taking %d samples with interval %d ms\n", sensor.samples, sensor.interval);
    
    // Read multiple samples
    for(int i = 0; i < sensor.samples; i++) {
        int rawValue = analogRead(sensor.pin);
        // Skip maximum ADC values as they might indicate disconnected or faulty sensor
        if (rawValue < 4095) {
            sum += rawValue;
            validSamples++;
            // Calculate and show voltage for each sample
            float voltage = rawValue * (3.3 / 4095.0);
            Serial.printf("[pH] Sample %d: Raw ADC = %d, Voltage = %.3fV\n", 
                i+1, rawValue, voltage);
        } else {
            Serial.printf("[pH] Sample %d: Raw ADC = %d (Skipped - Max value)\n", 
                i+1, rawValue);
        }
        delay(10);
    }
    
    // Check if we have any valid samples
    if (validSamples == 0) {
        Serial.println("[pH] Error: No valid samples collected - sensor might be disconnected");
        return -1;  // Return error value
    }
    
    float rawAverage = (float)sum / validSamples;
    float voltageAverage = rawAverage * (3.3 / 4095.0);
    Serial.printf("[pH] Average values - ADC: %.2f, Voltage: %.3fV\n", rawAverage, voltageAverage);
    
    // Find two closest calibration points
    int lower = 0;
    int upper = 1;
    
    // Since pH sensor typically gives higher ADC for lower pH
    // We need to handle the case where ADC is higher than pH 4 calibration point
    if (rawAverage > sensor.calibration[0][1]) {
        Serial.printf("[pH] ADC (%.2f) higher than pH %.2f calibration point (%.2f)\n", 
            rawAverage, sensor.calibration[0][0], sensor.calibration[0][1]);
        return 4.01;  // Return minimum pH
    }
    
    // And handle case where ADC is lower than pH 10 calibration point
    if (rawAverage < sensor.calibration[2][1]) {
        Serial.printf("[pH] ADC (%.2f) lower than pH %.2f calibration point (%.2f)\n", 
            rawAverage, sensor.calibration[2][0], sensor.calibration[2][1]);
        return 9.18;  // Return maximum pH
    }
    
    // Find the calibration points to interpolate between
    for(int i = 0; i < 2; i++) {
        if(rawAverage <= sensor.calibration[i][1] && 
           rawAverage >= sensor.calibration[i+1][1]) {
            lower = i;
            upper = i + 1;
            break;
        }
    }
    
    // Log calibration points being used
    Serial.printf("[pH] Using calibration points:\n");
    Serial.printf("  Lower: pH %.2f = ADC %.1f (%.3fV)\n", 
        sensor.calibration[lower][0], 
        sensor.calibration[lower][1],
        sensor.calibration[lower][1] * (3.3 / 4095.0));
    Serial.printf("  Upper: pH %.2f = ADC %.1f (%.3fV)\n", 
        sensor.calibration[upper][0], 
        sensor.calibration[upper][1],
        sensor.calibration[upper][1] * (3.3 / 4095.0));
    
    // Linear interpolation between calibration points
    float ph = sensor.calibration[lower][0] + 
               (rawAverage - sensor.calibration[lower][1]) * 
               (sensor.calibration[upper][0] - sensor.calibration[lower][0]) /
               (sensor.calibration[upper][1] - sensor.calibration[lower][1]);
               
    Serial.printf("[pH] Calculated values - pH: %.2f, ADC: %.2f, Voltage: %.3fV\n", 
        ph, rawAverage, voltageAverage);
    
    // Validate pH is in reasonable range
    if (ph >= 0 && ph <= 14) {
        return ph;
    } else {
        Serial.println("[pH] Error: Calculated pH out of valid range");
        return -1;
    }
}

// Add this function after Pin structure
bool isValidAnalogPin(int pin) {
    // Only ADC1 pins are valid when using WiFi
    return (pin == 32 || pin == 33 || pin == 34 || pin == 35 || pin == 36 || pin == 39);
}

// Update setupPin function
void setupPin(int pinNumber, int mode, const char* type) {
    if (pinNumber >= 0 && pinNumber < 40) {
        Serial.printf("[SETUP] Starting pin %d configuration as %s (%s)\n", 
            pinNumber, 
            mode == OUTPUT ? "OUTPUT" : mode == INPUT ? "INPUT" : "INPUT_PULLUP",
            type
        );
        
        // Validate analog pins
        if (strcmp(type, "analog_input") == 0) {
            if (!isValidAnalogPin(pinNumber)) {
                Serial.printf("[SETUP] ERROR: Pin %d is not a valid analog input pin when using WiFi\n", pinNumber);
                return;
            }
            // For analog pins, don't set pinMode
            analogReadResolution(12);  // Set ADC resolution to 12 bits
            analogSetAttenuation(ADC_11db);  // Set input attenuation for 3.3V range
            Serial.printf("[SETUP] Initialized ADC for pin %d\n", pinNumber);
        } else {
            // For digital pins, set pinMode
            pinMode(pinNumber, mode);
            if (mode == OUTPUT) {
                digitalWrite(pinNumber, LOW);
                Serial.printf("[SETUP] Pin %d initialized to LOW\n", pinNumber);
            }
        }
        
        pins[pinNumber].number = pinNumber;
        pins[pinNumber].mode = mode;
        pins[pinNumber].value = 0;
        pins[pinNumber].isConfigured = true;
        pins[pinNumber].type = String(type);
        
        Serial.printf("[SETUP] Pin %d configuration complete. isConfigured=%d, mode=%d, type=%s\n", 
            pinNumber, pins[pinNumber].isConfigured, pins[pinNumber].mode, pins[pinNumber].type.c_str());
    } else {
        Serial.printf("[SETUP] ERROR: Invalid pin number %d\n", pinNumber);
    }
}

// Fungsi untuk mengubah nilai pin
void setPinValue(int pinNumber, int value) {
    if (pinNumber >= 0 && pinNumber < 40 && pins[pinNumber].isConfigured) {
        if (pins[pinNumber].mode == OUTPUT) {
            Serial.printf("[PIN] Setting pin %d to %d\n", pinNumber, value);
            digitalWrite(pinNumber, value);
            pins[pinNumber].value = value;
            
            // Verifikasi perubahan pin
            int readValue = digitalRead(pinNumber);
            Serial.printf("[PIN] Pin %d verification: set to %d, read value is %d\n", 
                pinNumber, value, readValue);
            
            if (readValue != value) {
                Serial.printf("[PIN] WARNING: Pin %d state verification failed!\n", pinNumber);
            } else {
                Serial.printf("[PIN] Pin %d state change successful\n", pinNumber);
            }
        } else {
            Serial.printf("[PIN] ERROR: Pin %d is not configured as OUTPUT\n", pinNumber);
        }
    } else {
        Serial.printf("[PIN] ERROR: Invalid pin number %d or pin not configured\n", pinNumber);
    }
}

// Fungsi untuk membaca nilai pin
int readPinValue(int pinNumber) {
    if (pinNumber >= 0 && pinNumber < 40 && pins[pinNumber].isConfigured) {
        if (pins[pinNumber].mode == INPUT) {
            if (pins[pinNumber].type == "analog_input") {
                return analogRead(pinNumber);
            } else {
                return digitalRead(pinNumber);
            }
        }
    }
    return -1;
}

// Add new variables for reconnection
const unsigned long WEBSOCKET_RECONNECT_INTERVAL = 5000;  // 5 seconds
const unsigned long WIFI_RECONNECT_INTERVAL = 30000;      // 30 seconds
unsigned long lastWifiReconnectAttempt = 0;
unsigned long lastWebSocketReconnectAttempt = 0;
bool wasConnected = false;

// Improve checkConnection function
void checkConnection() {
    unsigned long currentMillis = millis();
    
    // Check WiFi connection
    if (WiFi.status() != WL_CONNECTED) {
        if (currentMillis - lastWifiReconnectAttempt >= WIFI_RECONNECT_INTERVAL) {
            Serial.println("[WIFI] Connection lost, reconnecting...");
            WiFi.disconnect();
            WiFi.begin(ssid, password);
            lastWifiReconnectAttempt = currentMillis;
        }
    }
    
    // If WiFi is connected but WebSocket isn't
    if (WiFi.status() == WL_CONNECTED && !isConnected) {
        if (currentMillis - lastWebSocketReconnectAttempt >= WEBSOCKET_RECONNECT_INTERVAL) {
            Serial.println("[WS] Attempting to reconnect...");
            webSocket.disconnect();
            webSocket.begin(ws_host, ws_port, "/");
            lastWebSocketReconnectAttempt = currentMillis;
        }
    }

    // Track connection state changes
    if (isConnected != wasConnected) {
        wasConnected = isConnected;
        if (isConnected) {
            Serial.println("[WS] Connection established");
        } else {
            Serial.println("[WS] Connection lost");
        }
    }
}

// Fungsi untuk membersihkan konfigurasi pin
void cleanupPin(int pinNumber) {
    if (pinNumber >= 0 && pinNumber < 40 && pins[pinNumber].isConfigured) {
        Serial.printf("[CLEANUP] Starting cleanup for pin %d\n", pinNumber);
        
        // Reset pin ke kondisi default
        if (pins[pinNumber].mode == OUTPUT) {
            digitalWrite(pinNumber, LOW);
            Serial.printf("[CLEANUP] Reset pin %d to LOW\n", pinNumber);
        }
        
        // Reset struktur data pin
        pins[pinNumber].isConfigured = false;
        pins[pinNumber].mode = INPUT;  // default mode
        pins[pinNumber].value = 0;
        pins[pinNumber].type = "";
        
        Serial.printf("[CLEANUP] Pin %d cleanup complete\n", pinNumber);
    } else {
        Serial.printf("[CLEANUP] Invalid pin number %d or pin not configured\n", pinNumber);
    }
}

// Update WebSocket message handler
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
    else if (strcmp(type, "config") == 0) {
        int pin = doc["pin"] | -1;
        const char* pinType = doc["pin_type"] | "unknown";
        const char* name = doc["name"] | "unnamed";
        
        Serial.printf("[MSG] Pin config message - Pin: %d, Type: %s, Name: %s\n", pin, pinType, name);
        
        if (pin >= 0) {
            int mode;
            bool configSuccess = true;
            
            if (strcmp(pinType, "digital_output") == 0) {
                mode = OUTPUT;
                Serial.printf("[MSG] Configuring pin %d as OUTPUT\n", pin);
            } else if (strcmp(pinType, "digital_input") == 0) {
                mode = INPUT;
                Serial.printf("[MSG] Configuring pin %d as INPUT\n", pin);
            } else if (strcmp(pinType, "analog_input") == 0) {
                mode = INPUT;
                Serial.printf("[MSG] Configuring pin %d as ANALOG INPUT\n", pin);
                
                // Initialize ADC for this pin
                analogSetWidth(12);  // Set ADC resolution to 12 bits (0-4095)
                analogSetAttenuation(ADC_11db);  // Set ADC attenuation for 3.3V range
            } else {
                Serial.printf("[MSG] Error: Invalid pin type '%s'\n", pinType);
                configSuccess = false;
            }
            
            if (configSuccess) {
                setupPin(pin, mode, pinType);
                
                // Kirim konfirmasi konfigurasi
                StaticJsonDocument<200> response;
                response["type"] = "pin_configured";
                response["pin"] = pin;
                response["pin_type"] = pinType;
                response["name"] = name;
                response["status"] = "success";
                String responseMsg;
                serializeJson(response, responseMsg);
                Serial.printf("[MSG] Sending configuration confirmation: %s\n", responseMsg.c_str());
                webSocket.sendTXT(responseMsg);
            } else {
                // Kirim error jika konfigurasi gagal
                StaticJsonDocument<200> response;
                response["type"] = "error";
                response["message"] = "Invalid pin configuration";
                response["pin"] = pin;
                String responseMsg;
                serializeJson(response, responseMsg);
                Serial.printf("[MSG] Sending error response: %s\n", responseMsg.c_str());
                webSocket.sendTXT(responseMsg);
            }
        } else {
            Serial.println("[MSG] Invalid pin number in config message");
        }
    }
    else if (strcmp(type, "pin_config") == 0) {
        int pin = doc["pin"] | -1;
        const char* pinTypeStr = doc["settings"]["type"] | "unknown";
        String pinType = String(pinTypeStr);
        
        if (pinType == "ph_sensor") {
            Serial.printf("\n[pH] Received configuration for pin %d\n", pin);
            
            int samples = doc["settings"]["samples"] | 10;
            int interval = doc["settings"]["interval"] | 1000;
            
            Serial.printf("[pH] Settings - samples: %d, interval: %d ms\n", samples, interval);
            
            if(addPhSensor(pin, samples, interval)) {
                int index = findPhSensor(pin);
                
                // Get calibration values from settings
                JsonObject calibration = doc["settings"]["calibration"];
                if (!calibration.isNull()) {
                    Serial.println("[pH] Received calibration values:");
                    float cal4 = calibration["4"] | 4090.0f;  // pH 4.01 = 4090-4095
                    float cal7 = calibration["7"] | 3140.0f;  // pH 6.86 = ~3140
                    float cal10 = calibration["10"] | 2350.0f;  // pH 9.18 = 2350-2400
                    
                    Serial.printf("  pH 4.01: ADC = %.1f (default: 4090.0)\n", cal4);
                    Serial.printf("  pH 6.86: ADC = %.1f (default: 3140.0)\n", cal7);
                    Serial.printf("  pH 9.18: ADC = %.1f (default: 2350.0)\n", cal10);
                    
                    updatePhCalibration(pin, cal4, cal7, cal10);
                } else {
                    Serial.println("[pH] Warning: No calibration data received, using defaults");
                }
                
                // Configure pin
                pinMode(pin, INPUT);
                
                // Send confirmation
                StaticJsonDocument<200> response;
                response["type"] = "pin_config_response";
                response["pin"] = pin;
                response["status"] = "success";
                response["message"] = "pH sensor configured on pin " + String(pin);
                
                String responseStr;
                serializeJson(response, responseStr);
                webSocket.sendTXT(responseStr);
            } else {
                // Send error - maximum number of sensors reached
                StaticJsonDocument<200> response;
                response["type"] = "pin_config_response";
                response["pin"] = pin;
                response["status"] = "error";
                response["message"] = "Maximum number of pH sensors reached";
                
                String responseStr;
                serializeJson(response, responseStr);
                webSocket.sendTXT(responseStr);
            }
        }
    }
    else if (strcmp(type, "cleanup_pin") == 0) {
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
    else if (strcmp(type, "reboot") == 0) {
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
}

void webSocketEvent(WStype_t type, uint8_t * payload, size_t length) {
    switch(type) {
        case WStype_CONNECTED:
            Serial.println("[WS] Connected to server");
            isConnected = true;
            // Kirim pesan autentikasi dengan format yang benar
            {
                StaticJsonDocument<200> doc;
                doc["type"] = "auth";
                doc["device_key"] = device_key;
                String message;
                serializeJson(doc, message);
                Serial.println("[WS] Sending auth message: " + message);
                webSocket.sendTXT(message);
            }
            break;
            
        case WStype_TEXT:
            Serial.printf("[WS] Received text message (length: %d): ", length);
            if (payload) {
                Serial.println((char*)payload);
                handleWebSocketMessage(payload);
            } else {
                Serial.println("[WS] Error: Empty payload");
            }
            break;
            
        case WStype_DISCONNECTED:
            Serial.println("[WS] Disconnected from server");
            isConnected = false;
            break;
            
        case WStype_ERROR:
            Serial.println("[WS] Error occurred");
            isConnected = false;
            break;
            
        case WStype_PING:
            Serial.println("[WS] Received ping");
            // Send pong response
            {
                StaticJsonDocument<200> response;
                response["type"] = "pong";
                response["timestamp"] = millis();
                String responseMsg;
                serializeJson(response, responseMsg);
                webSocket.sendTXT(responseMsg);
                Serial.println("[WS] Sent pong response");
            }
            break;
            
        case WStype_PONG:
            Serial.println("[WS] Received pong");
            break;
    }
}

// Struktur untuk pin analog
struct AnalogPin {
    unsigned long lastRead = 0;
    const unsigned long readInterval = 1000; // Read every 1 second
};

AnalogPin analogPins[40];

// Update handleAnalogPins function
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

void loop() {
    // Handle WebSocket events
    webSocket.loop();
    
    // Check and handle reconnection
    checkConnection();
    
    if (isConnected) {
        // Handle pH sensors
        for(int i = 0; i < numPhSensors; i++) {
            if(phSensors[i].isConfigured && 
               (millis() - phSensors[i].lastRead >= phSensors[i].interval)) {
                
                Serial.printf("\n[pH] Reading sensor %d on pin %d\n", i, phSensors[i].pin);
                Serial.printf("[pH] Calibration values:\n");
                Serial.printf("  pH 4.01: ADC = %.1f\n", phSensors[i].calibration[0][1]);
                Serial.printf("  pH 6.86: ADC = %.1f\n", phSensors[i].calibration[1][1]);
                Serial.printf("  pH 9.18: ADC = %.1f\n", phSensors[i].calibration[2][1]);
                
                float ph = readPH(phSensors[i]);
                int rawADC = analogRead(phSensors[i].pin);
                
                // Only update and send if we got a valid reading
                if (ph > 0 && ph <= 14) {  // pH values are always between 0-14
                    phSensors[i].lastValue = ph;
                    phSensors[i].lastRead = millis();
                    
                    // Send pH reading with raw ADC value
                    StaticJsonDocument<200> doc;
                    doc["type"] = "sensor_data";
                    doc["device_key"] = device_key;
                    doc["pin"] = phSensors[i].pin;
                    doc["value"] = ph;
                    doc["raw_adc"] = rawADC;
                    doc["timestamp"] = millis();
                    
                    String message;
                    serializeJson(doc, message);
                    webSocket.sendTXT(message);
                    
                    Serial.printf("[pH] Sent reading: pH=%.2f, Raw ADC=%d\n", ph, rawADC);
                } else {
                    Serial.printf("[pH] Invalid reading (pH=%.2f), skipping update\n", ph);
                    
                    // Send error status
                    StaticJsonDocument<200> doc;
                    doc["type"] = "sensor_data";
                    doc["device_key"] = device_key;
                    doc["pin"] = phSensors[i].pin;
                    doc["value"] = -1;  // Error indicator
                    doc["raw_adc"] = rawADC;
                    doc["timestamp"] = millis();
                    doc["error"] = "Invalid pH reading";
                    
                    String message;
                    serializeJson(doc, message);
                    webSocket.sendTXT(message);
                }
            }
        }
        
        // Handle analog pins
        handleAnalogPins();
        
        // Monitor pin input
        for (int i = 0; i < 40; i++) {
            if (pins[i].isConfigured) {
                if (pins[i].mode == INPUT || pins[i].mode == INPUT_PULLUP) {
                    int currentValue = readPinValue(i);
                    if (currentValue != pins[i].value) {
                        pins[i].value = currentValue;
                        
                        // Kirim update ke server
                        StaticJsonDocument<200> doc;
                        doc["type"] = "update";
                        doc["device_key"] = device_key;  // Add device_key
                        doc["pin"] = i;
                        doc["value"] = currentValue;
                        
                        String message;
                        serializeJson(doc, message);
                        webSocket.sendTXT(message);
                    }
                }
            }
        }
    }
    
    // Small delay to prevent overwhelming the system
    delay(100);
}
</code></pre>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function copyDeviceCode() {
    const codeElement = document.getElementById('deviceCode');
    const textarea = document.createElement('textarea');
    textarea.value = codeElement.textContent;
    document.body.appendChild(textarea);
    textarea.select();
    document.execCommand('copy');
    document.body.removeChild(textarea);
    
    // Show feedback
    const button = document.querySelector('button[onclick="copyDeviceCode()"]');
    const originalText = button.innerHTML;
    button.innerHTML = '<i class="fas fa-check"></i> Copied!';
    setTimeout(() => {
        button.innerHTML = originalText;
    }, 2000);
}
</script>
@endpush
@endsection 