// Digital pin functions
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