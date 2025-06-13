// Function to read pH value
float readPH(PHSensor &sensor) {
    // Initialize ADC for pH sensor
    analogSetWidth(12);  // Set ADC resolution to 12 bits (0-4095)
    analogSetAttenuation(ADC_11db);  // Set ADC attenuation for 3.3V range
    
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