// Cleanup functions
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