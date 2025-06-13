// Validation functions
bool isValidAnalogPin(int pin) {
    // Only ADC1 pins are valid when using WiFi
    return (pin == 32 || pin == 33 || pin == 34 || pin == 35 || pin == 36 || pin == 39);
} 