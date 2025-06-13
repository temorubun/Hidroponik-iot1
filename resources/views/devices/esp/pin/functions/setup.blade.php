// Setup pin function
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