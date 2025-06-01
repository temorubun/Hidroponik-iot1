@php
/**
 * Pin Template for ESP32 Devices
 * Parameters:
 * - $pin: The pin object containing:
 *   - number: Pin number
 *   - mode: Pin mode (INPUT, OUTPUT, INPUT_PULLUP, ANALOG)
 *   - function: Pin function (e.g., 'digital_output', 'digital_input', 'analog_input', etc.)
 *   - name: Human readable name for the pin
 */
@endphp

{{-- Pin Configuration Template --}}
// Pin {{ $pin['number'] }} Configuration - {{ $pin['name'] }}
const int PIN_{{ $pin['number'] }} = {{ $pin['number'] }};

@if($pin['function'] === 'digital_output')
// Digital Output Pin Setup
void handlePin{{ $pin['number'] }}(bool state) {
    Serial.print("[PIN {{ $pin['number'] }}] Setting {{ $pin['name'] }} to: ");
    Serial.println(state ? "HIGH" : "LOW");
    
    digitalWrite(PIN_{{ $pin['number'] }}, state ? HIGH : LOW);
    
    // Verify the pin state
    bool currentState = digitalRead(PIN_{{ $pin['number'] }}) == HIGH;
    Serial.print("[PIN {{ $pin['number'] }}] Verified state: ");
    Serial.println(currentState ? "HIGH" : "LOW");
    
    if (currentState != state) {
        Serial.println("[PIN {{ $pin['number'] }}] WARNING: Pin state verification failed!");
    } else {
        Serial.println("[PIN {{ $pin['number'] }}] State change successful");
    }
}
@endif

@if($pin['function'] === 'digital_input')
// Digital Input Pin Setup
int lastPin{{ $pin['number'] }}State = -1;

// Function to read pin {{ $pin['number'] }} state
void readPin{{ $pin['number'] }}() {
    int currentState = digitalRead(PIN_{{ $pin['number'] }});
    if (currentState != lastPin{{ $pin['number'] }}State) {
        lastPin{{ $pin['number'] }}State = currentState;
        StaticJsonDocument<200> doc;
        doc["type"] = "pin_update";
        doc["pin_number"] = {{ $pin['number'] }};
        doc["value"] = currentState;
        String message;
        serializeJson(doc, message);
        webSocket.sendTXT(message);
    }
}
@endif

@if($pin['function'] === 'analog_input')
// Analog Input Pin Setup
const unsigned long PIN_{{ $pin['number'] }}_INTERVAL = 1000; // Read every 1 second
unsigned long lastPin{{ $pin['number'] }}Read = 0;

// Function to read analog pin {{ $pin['number'] }} value
void readAnalogPin{{ $pin['number'] }}() {
    unsigned long currentMillis = millis();
    if (currentMillis - lastPin{{ $pin['number'] }}Read >= PIN_{{ $pin['number'] }}_INTERVAL) {
        lastPin{{ $pin['number'] }}Read = currentMillis;
        int value = analogRead(PIN_{{ $pin['number'] }});
        StaticJsonDocument<200> doc;
        doc["type"] = "sensor_data";
        doc["pin_number"] = {{ $pin['number'] }};
        doc["value"] = value;
        String message;
        serializeJson(doc, message);
        webSocket.sendTXT(message);
    }
}
@endif 