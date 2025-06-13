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