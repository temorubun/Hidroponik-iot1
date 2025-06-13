// pH sensor core functions
int findPhSensor(int pin) {
    for(int i = 0; i < numPhSensors; i++) {
        if(phSensors[i].pin == pin) {
            return i;
        }
    }
    return -1;
}

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