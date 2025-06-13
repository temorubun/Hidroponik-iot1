// Pin configuration message handler
void handlePinConfigMessage(const JsonDocument& doc) {
    int pin = doc["pin"] | -1;
    const char* pinTypeStr = doc["settings"]["type"] | "unknown";
    String pinType = String(pinTypeStr);
    
    if (pinType == "ph_sensor") {
        Serial.printf("\n[pH] Received configuration for pin %d\n", pin);
        
        // Initialize ADC for pH sensor
        analogSetWidth(12);  // Set ADC resolution to 12 bits (0-4095)
        analogSetAttenuation(ADC_11db);  // Set ADC attenuation for 3.3V range
        
        int samples = doc["settings"]["samples"] | 10;  // Default to 10 samples
        int interval = doc["settings"]["interval"] | 1000;  // Default to 1000ms
        
        Serial.printf("[pH] Settings - samples: %d, interval: %d ms\n", samples, interval);
        
        if(addPhSensor(pin, samples, interval)) {
            int index = findPhSensor(pin);
            
            // Get calibration values from settings
            if (!doc["settings"]["calibration"].isNull()) {
                Serial.println("[pH] Received calibration values:");
                
                // Default calibration values with | operator
                float cal4 = doc["settings"]["calibration"]["4"] | 0.0f;   // pH 4.01 = 0
                float cal7 = doc["settings"]["calibration"]["7"] | 0.0f;   // pH 6.86 = 0
                float cal10 = doc["settings"]["calibration"]["10"] | 0.0f;  // pH 9.18 = 0
                
                Serial.printf("  pH 4.01: ADC = %.1f (default: 0.0)\n", cal4);
                Serial.printf("  pH 6.86: ADC = %.1f (default: 0.0)\n", cal7);
                Serial.printf("  pH 9.18: ADC = %.1f (default: 0.0)\n", cal10);
                
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
            response["pin_type"] = "ph_sensor";
            response["status"] = "success";
            String responseMsg;
            serializeJson(response, responseMsg);
            webSocket.sendTXT(responseMsg);
            
            Serial.println("[pH] Configuration completed successfully");
        } else {
            Serial.println("[pH] Error: Failed to add pH sensor");
            // Send error response
            StaticJsonDocument<200> response;
            response["type"] = "pin_config_response";
            response["pin"] = pin;
            response["pin_type"] = "ph_sensor";
            response["status"] = "error";
            response["message"] = "Failed to add pH sensor";
            String responseMsg;
            serializeJson(response, responseMsg);
            webSocket.sendTXT(responseMsg);
        }
    } else {
        // For other pin types, delegate to the standard config handler
        handleConfigMessage(doc);
    }
} 