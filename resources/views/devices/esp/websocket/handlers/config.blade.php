// Config message handler
void handleConfigMessage(const JsonDocument& doc) {
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
        } else if (strcmp(pinType, "analog_input") == 0 || strcmp(pinType, "ph_sensor") == 0) {
            mode = INPUT;
            Serial.printf("[MSG] Configuring pin %d as %s\n", pin, pinType);
            
            // Initialize ADC for this pin
            analogSetWidth(12);  // Set ADC resolution to 12 bits (0-4095)
            analogSetAttenuation(ADC_11db);  // Set ADC attenuation for 3.3V range
            
            // Additional setup for pH sensor
            if (strcmp(pinType, "ph_sensor") == 0) {
                int samples = doc["settings"]["samples"] | 10;  // Default to 10 samples
                int interval = doc["settings"]["interval"] | 1000;  // Default to 1000ms
                
                if (addPhSensor(pin, samples, interval)) {
                    // Get calibration values if provided
                    if (!doc["settings"]["calibration"].isNull()) {
                        float cal4 = doc["settings"]["calibration"]["4"] | 4090.0f;
                        float cal7 = doc["settings"]["calibration"]["7"] | 3140.0f;
                        float cal10 = doc["settings"]["calibration"]["10"] | 2350.0f;
                        updatePhCalibration(pin, cal4, cal7, cal10);
                    }
                }
            }
        } else {
            Serial.printf("[MSG] Error: Invalid pin type '%s'\n", pinType);
            configSuccess = false;
        }
        
        if (configSuccess) {
            setupPin(pin, mode, pinType);
            
            // Send configuration confirmation
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
            // Send error response
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