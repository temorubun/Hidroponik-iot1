@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Edit Pin: {{ $pin->name }}</h5>
                    <a href="{{ route('devices.show', $device) }}" class="btn btn-sm btn-outline-secondary">
                        <i class="fas fa-arrow-left"></i> Back
                    </a>
                </div>

                <div class="card-body">
                    <form method="POST" action="{{ route('pins.update', [$device, $pin]) }}">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label for="name" class="form-label">Pin Name</label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                id="name" name="name" value="{{ old('name', $pin->name) }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="pin_number" class="form-label">Pin Number</label>
                            <select class="form-select @error('pin_number') is-invalid @enderror" 
                                id="pin_number" name="pin_number" required>
                                <option value="">Select GPIO Pin</option>
                            </select>
                            @error('pin_number')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="type" class="form-label">Pin Type</label>
                            <select class="form-select @error('type') is-invalid @enderror" 
                                id="type" name="type" required onchange="updateSettings()">
                                <option value="">Select Pin Type</option>
                                <option value="digital_output" {{ old('type', $pin->type) == 'digital_output' ? 'selected' : '' }}>
                                    Digital Output (e.g. relay, LED)
                                </option>
                                <option value="digital_input" {{ old('type', $pin->type) == 'digital_input' ? 'selected' : '' }}>
                                    Digital Input (e.g. button, switch)
                                </option>
                                <option value="analog_input" {{ old('type', $pin->type) == 'analog_input' ? 'selected' : '' }}>
                                    Analog Input (e.g. sensors)
                                </option>
                                <option value="ph_sensor" {{ old('type', $pin->type) == 'ph_sensor' ? 'selected' : '' }}>
                                    pH Sensor
                                </option>
                            </select>
                            @error('type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <input type="text" class="form-control @error('settings.description') is-invalid @enderror" 
                                id="description" name="settings[description]" 
                                value="{{ old('settings.description', $pin->settings['description'] ?? '') }}">
                        </div>

                        <div class="mb-3">
                            <label for="icon" class="form-label">Icon</label>
                            <select class="form-select @error('settings.icon') is-invalid @enderror" 
                                id="icon" name="settings[icon]">
                                <option value="">Select Icon</option>
                                <option value="lightbulb" {{ ($pin->settings['icon'] ?? '') == 'lightbulb' ? 'selected' : '' }}>
                                    💡 Lightbulb
                                </option>
                                <option value="water" {{ ($pin->settings['icon'] ?? '') == 'water' ? 'selected' : '' }}>
                                    💧 Water/Pump
                                </option>
                                <option value="temperature-half" {{ ($pin->settings['icon'] ?? '') == 'temperature-half' ? 'selected' : '' }}>
                                    🌡️ Temperature
                                </option>
                                <option value="vial" {{ ($pin->settings['icon'] ?? '') == 'vial' ? 'selected' : '' }}>
                                    🧪 Chemical/pH
                                </option>
                                <option value="flask" {{ ($pin->settings['icon'] ?? '') == 'flask' ? 'selected' : '' }}>
                                    🔬 TDS/PPM
                                </option>
                                <option value="wind" {{ ($pin->settings['icon'] ?? '') == 'wind' ? 'selected' : '' }}>
                                    💨 Air/Oxygen
                                </option>
                            </select>
                        </div>

                        <!-- Schedule Settings (for digital output) -->
                        <div id="scheduleSettings" style="display: none;">
                            <h6 class="mb-3">Schedule Settings</h6>
                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="useSchedule" 
                                        name="settings[schedule][enabled]" value="1"
                                        {{ isset($pin->settings['schedule']) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="useSchedule">
                                        Enable Schedule
                                    </label>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col">
                                    <label for="scheduleOn" class="form-label">Start Time</label>
                                    <input type="time" class="form-control" id="scheduleOn" 
                                        name="settings[schedule][on]"
                                        value="{{ $pin->settings['schedule']['on'] ?? '' }}">
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col">
                                    <label for="duration" class="form-label">ON Duration (minutes)</label>
                                    <input type="number" class="form-control" id="duration" 
                                        name="settings[schedule][duration]"
                                        value="{{ $pin->settings['schedule']['duration'] ?? '' }}"
                                        placeholder="e.g. 5">
                                    <small class="text-muted">How long the pin stays ON in each cycle</small>
                                </div>
                                <div class="col">
                                    <label for="interval" class="form-label">Cycle Interval (minutes)</label>
                                    <input type="number" class="form-control" id="interval" 
                                        name="settings[schedule][interval]"
                                        value="{{ $pin->settings['schedule']['interval'] ?? '' }}"
                                        placeholder="e.g. 10">
                                    <small class="text-muted">Total time for one ON/OFF cycle</small>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col">
                                    <label for="cycleDuration" class="form-label">Cycle Duration (minutes)</label>
                                    <input type="number" class="form-control" id="cycleDuration" 
                                        name="settings[schedule][cycle_duration]"
                                        value="{{ $pin->settings['schedule']['cycle_duration'] ?? '' }}"
                                        placeholder="e.g. 30">
                                    <small class="text-muted">How long the cycles should continue (e.g. 30 minutes)</small>
                                </div>
                            </div>
                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="repeatHourly" 
                                        name="settings[schedule][repeat_hourly]" value="1"
                                        {{ isset($pin->settings['schedule']['repeat_hourly']) && $pin->settings['schedule']['repeat_hourly'] ? 'checked' : '' }}>
                                    <label class="form-check-label" for="repeatHourly">
                                        Repeat Schedule
                                    </label>
                                </div>
                            </div>
                            <div class="mb-3" id="hourlyIntervalSection" style="display: none;">
                                <label for="hourlyInterval" class="form-label">Repeat Every (minutes)</label>
                                <input type="number" class="form-control" id="hourlyInterval" 
                                    name="settings[schedule][hourly_interval]"
                                    value="{{ $pin->settings['schedule']['hourly_interval'] ?? '' }}"
                                    placeholder="e.g. 180 for 3 hours">
                                <small class="text-muted">Enter how often the schedule should repeat (e.g. 180 for every 3 hours)</small>
                            </div>
                        </div>

                        <!-- pH Sensor Settings -->
                        <div id="phSensorSettings" style="display: none;">
                            <h6 class="mb-3">pH Sensor Settings</h6>
                            
                            <div class="mb-3">
                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" id="useDefaultValues" 
                                        onchange="toggleDefaultValues(this.checked)">
                                    <label class="form-check-label" for="useDefaultValues">
                                        Use Default Values
                                    </label>
                                </div>
                            </div>

                            <h6 class="mb-3">Calibration Points</h6>
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <div class="input-group">
                                        <span class="input-group-text">pH 4.0</span>
                                        <input type="number" class="form-control" id="cal4" 
                                            name="settings[calibration][4]"
                                            value="{{ $pin->settings['calibration']['4'] ?? '' }}"
                                            placeholder="Raw ADC value">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="input-group">
                                        <span class="input-group-text">pH 7.0</span>
                                        <input type="number" class="form-control" id="cal7" 
                                            name="settings[calibration][7]"
                                            value="{{ $pin->settings['calibration']['7'] ?? '' }}"
                                            placeholder="Raw ADC value">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="input-group">
                                        <span class="input-group-text">pH 10.0</span>
                                        <input type="number" class="form-control" id="cal10" 
                                            name="settings[calibration][10]"
                                            value="{{ $pin->settings['calibration']['10'] ?? '' }}"
                                            placeholder="Raw ADC value">
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <div class="input-group">
                                        <span class="input-group-text">Samples</span>
                                        <input type="number" class="form-control" id="samples" 
                                            name="settings[samples]"
                                            value="{{ $pin->settings['samples'] ?? 10 }}"
                                            min="1" max="100" placeholder="10">
                                    </div>
                                    <small class="text-muted">Number of samples to average (1-100)</small>
                                </div>
                                <div class="col-md-6">
                                    <div class="input-group">
                                        <span class="input-group-text">Interval</span>
                                        <input type="number" class="form-control" id="interval" 
                                            name="settings[interval]"
                                            value="{{ $pin->settings['interval'] ?? 1000 }}"
                                            min="100" placeholder="1000">
                                        <span class="input-group-text">ms</span>
                                    </div>
                                    <small class="text-muted">Reading interval (min 100ms)</small>
                                </div>
                            </div>

                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i> <strong>Important:</strong> For pH sensors, use ADC1 pins (GPIO32-39) for better accuracy.<br>
                                Recommended pins: GPIO36, GPIO39, GPIO34, or GPIO35.
                            </div>
                        </div>

                        <!-- Alert Settings (for all pin types) -->
                        <div id="alertSettings">
                            <h6 class="mb-3">Telegram Alert Settings</h6>
                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="useAlerts" 
                                        name="settings[alerts][enabled]" value="1"
                                        {{ isset($pin->settings['alerts']['enabled']) && $pin->settings['alerts']['enabled'] ? 'checked' : '' }}>
                                    <label class="form-check-label" for="useAlerts">
                                        Enable Telegram Alerts
                                    </label>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="telegramChatId" class="form-label">Telegram Chat ID</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="telegramChatId" 
                                        name="settings[alerts][telegram_chat_id]" 
                                        placeholder="e.g. 123456789"
                                        value="{{ $pin->settings['alerts']['telegram_chat_id'] ?? '' }}">
                                    <button type="button" class="btn btn-outline-primary" onclick="testTelegramConnection()">
                                        Test Connection
                                    </button>
                                </div>
                                <small class="text-muted">This is required to receive alerts on Telegram</small>
                                <div id="telegramTestResult" class="mt-2" style="display: none;"></div>
                            </div>

                            <!-- Digital Input Alert Settings -->
                            <div id="digitalInputAlerts" style="display: none;">
                                <div class="mb-3">
                                    <label class="form-label">Alert on State Change</label>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" 
                                            name="settings[alerts][on_high]" value="1" 
                                            {{ isset($pin->settings['alerts']['on_high']) && $pin->settings['alerts']['on_high'] ? 'checked' : '' }}>
                                        <label class="form-check-label">Alert when pin goes HIGH</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" 
                                            name="settings[alerts][on_low]" value="1"
                                            {{ isset($pin->settings['alerts']['on_low']) && $pin->settings['alerts']['on_low'] ? 'checked' : '' }}>
                                        <label class="form-check-label">Alert when pin goes LOW</label>
                                    </div>
                                </div>
                            </div>

                            <!-- Digital Output Alert Settings -->
                            <div id="digitalOutputAlerts" style="display: none;">
                                <div class="mb-3">
                                    <label class="form-label">Alert on State Change</label>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" 
                                            name="settings[alerts][on_turn_on]" value="1"
                                            {{ isset($pin->settings['alerts']['on_turn_on']) && $pin->settings['alerts']['on_turn_on'] ? 'checked' : '' }}>
                                        <label class="form-check-label">Alert when turned ON</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" 
                                            name="settings[alerts][on_turn_off]" value="1"
                                            {{ isset($pin->settings['alerts']['on_turn_off']) && $pin->settings['alerts']['on_turn_off'] ? 'checked' : '' }}>
                                        <label class="form-check-label">Alert when turned OFF</label>
                                    </div>
                                </div>
                            </div>

                            <!-- Analog Input Alert Settings -->
                            <div id="analogInputAlerts" style="display: none;">
                                <div class="row mb-3">
                                    <div class="col">
                                        <label for="minThreshold" class="form-label">Min Threshold</label>
                                        <input type="number" step="0.1" class="form-control" id="minThreshold" 
                                            name="settings[alerts][min_threshold]"
                                            value="{{ $pin->settings['alerts']['min_threshold'] ?? '' }}">
                                        <div class="form-check mt-2">
                                            <input class="form-check-input" type="checkbox" 
                                                name="settings[alerts][alert_below_min]" value="1"
                                                {{ isset($pin->settings['alerts']['alert_below_min']) && $pin->settings['alerts']['alert_below_min'] ? 'checked' : '' }}>
                                            <label class="form-check-label">Alert when below min threshold</label>
                                        </div>
                                        <small class="text-muted" id="sensorThresholdHelp">
                                            <span id="phHelp" style="display: none;">For pH sensor: Enter value in pH units (0-14)</span>
                                            <span id="tempHelp" style="display: none;">For temperature: Enter value in °C (e.g. 25.0)</span>
                                            <span id="humidHelp" style="display: none;">For humidity: Enter percentage (0-100)</span>
                                            <span id="analogHelp" style="display: none;">For analog sensor: Enter raw value (0-4095)</span>
                                        </small>
                                    </div>
                                    <div class="col">
                                        <label for="maxThreshold" class="form-label">Max Threshold</label>
                                        <input type="number" step="0.1" class="form-control" id="maxThreshold" 
                                            name="settings[alerts][max_threshold]"
                                            value="{{ $pin->settings['alerts']['max_threshold'] ?? '' }}">
                                        <div class="form-check mt-2">
                                            <input class="form-check-input" type="checkbox" 
                                                name="settings[alerts][alert_above_max]" value="1"
                                                {{ isset($pin->settings['alerts']['alert_above_max']) && $pin->settings['alerts']['alert_above_max'] ? 'checked' : '' }}>
                                            <label class="form-check-label">Alert when above max threshold</label>
                                        </div>
                                    </div>
                                </div>

                                <!-- Sensor Type Specific Guidelines -->
                                <div class="alert alert-info" id="phThresholdInfo" style="display: none;">
                                    <i class="fas fa-info-circle"></i> <strong>pH Sensor Guide:</strong><br>
                                    - Normal pH range: 6.0 - 7.0<br>
                                    - Values below 7 are acidic, above 7 are basic/alkaline<br>
                                    - Example: Set Min = 5.5 and Max = 7.5 to get alerts when pH is too acidic or too basic
                                </div>

                                <div class="alert alert-info" id="dhtThresholdInfo" style="display: none;">
                                    <i class="fas fa-info-circle"></i> <strong>Temperature & Humidity Guide:</strong><br>
                                    - Temperature typical range: 20°C - 30°C<br>
                                    - Humidity typical range: 40% - 70%<br>
                                    - Example for temperature: Min = 18°C, Max = 32°C<br>
                                    - Example for humidity: Min = 30%, Max = 80%
                                </div>

                                <div class="alert alert-info" id="analogThresholdInfo" style="display: none;">
                                    <i class="fas fa-info-circle"></i> <strong>Analog Sensor Guide:</strong><br>
                                    - Raw ADC values range: 0 - 4095<br>
                                    - Higher value usually means stronger/higher reading<br>
                                    - Set thresholds based on your sensor's specifications
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="messageTemplate" class="form-label">Message Template</label>
                                <div class="mb-2">
                                    <select class="form-select" id="messageTemplate" onchange="updateAlertMessage()">
                                        <option value="">Select Template</option>
                                        <option value="basic">Basic Status</option>
                                        <option value="detailed">Detailed Status</option>
                                        <option value="schedule">Schedule Status</option>
                                        <option value="threshold">Threshold Alert</option>
                                        <option value="custom">Custom Template</option>
                                    </select>
                                </div>
                                <textarea class="form-control" id="alertMessage" 
                                    name="settings[alerts][message_template]" rows="3" 
                                    placeholder="e.g. {device_name} - {pin_name} is now {status}">{{ $pin->settings['alerts']['message_template'] ?? '' }}</textarea>
                                <small class="text-muted">
                                    Available variables: {device_name}, {pin_name}, {status}, {value}, {threshold}, {time}, {date}
                                </small>
                            </div>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Update Pin
                            </button>
                            
                            <button type="button" class="btn btn-danger" onclick="confirmDelete()">
                                <i class="fas fa-trash"></i> Delete Pin
                            </button>
                        </div>
                    </form>

                    <form id="delete-form" action="{{ route('pins.destroy', [$device, $pin]) }}" method="POST" class="d-none">
                        @csrf
                        @method('DELETE')
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Define available pins for each type
const pinTypes = {
    digital_output: [2, 4, 5, 12, 13, 14, 15, 16, 17, 18, 19, 21, 22, 23, 25, 26, 27, 32, 33],
    digital_input: [2, 4, 5, 12, 13, 14, 15, 16, 17, 18, 19, 21, 22, 23, 25, 26, 27, 32, 33, 34, 35, 36, 39],
    analog_input: [32, 33, 34, 35, 36, 39],
    ph_sensor: [32, 33, 34, 35, 36, 39]
};

// Store current pin number
const currentPinNumber = {{ $pin->pin_number }};

// Store used pin numbers for the current device (excluding current pin)
const usedPins = @json($device->pins->where('id', '!=', $pin->id)->pluck('pin_number')->toArray());

function updatePinNumbers() {
    const type = document.getElementById('type').value;
    const pinSelect = document.getElementById('pin_number');
    
    // Clear current options
    pinSelect.innerHTML = '<option value="">Select GPIO Pin</option>';
    
    if (type) {
        // Get available pins for selected type
        const availablePins = pinTypes[type];
        
        // Add options for available pins
        availablePins.forEach(pin => {
            // Include pin if it's the current pin or not used by other pins
            if (pin === currentPinNumber || !usedPins.includes(pin)) {
                const option = document.createElement('option');
                option.value = pin;
                option.textContent = `GPIO${pin}${pin >= 34 ? ' (Input Only)' : ''}`;
                option.selected = pin === currentPinNumber;
                pinSelect.appendChild(option);
            }
        });

        // If current pin is not valid for this type, show warning
        if (!availablePins.includes(currentPinNumber)) {
            alert('Warning: Current pin number is not recommended for selected pin type. Please select a different pin.');
        }
    }
}

function updateSettings() {
    const pinType = document.getElementById('type').value;
    const scheduleSettings = document.getElementById('scheduleSettings');
    const digitalOutputAlerts = document.getElementById('digitalOutputAlerts');
    const analogInputAlerts = document.getElementById('analogInputAlerts');
    const phSensorSettings = document.getElementById('phSensorSettings');
    
    // Get all threshold info elements
    const phThresholdInfo = document.getElementById('phThresholdInfo');
    const dhtThresholdInfo = document.getElementById('dhtThresholdInfo');
    const analogThresholdInfo = document.getElementById('analogThresholdInfo');
    
    // Get all help text spans
    const phHelp = document.getElementById('phHelp');
    const tempHelp = document.getElementById('tempHelp');
    const humidHelp = document.getElementById('humidHelp');
    const analogHelp = document.getElementById('analogHelp');
    
    // Hide all settings and info elements first
    scheduleSettings.style.display = 'none';
    digitalOutputAlerts.style.display = 'none';
    analogInputAlerts.style.display = 'none';
    phSensorSettings.style.display = 'none';
    phThresholdInfo.style.display = 'none';
    dhtThresholdInfo.style.display = 'none';
    analogThresholdInfo.style.display = 'none';
    
    // Hide all help text
    phHelp.style.display = 'none';
    tempHelp.style.display = 'none';
    humidHelp.style.display = 'none';
    analogHelp.style.display = 'none';
    
    // Show relevant settings based on pin type
    switch(pinType) {
        case 'digital_output':
            scheduleSettings.style.display = 'block';
            digitalOutputAlerts.style.display = 'block';
            break;
        case 'digital_input':
            break;
        case 'analog_input':
            analogInputAlerts.style.display = 'block';
            analogThresholdInfo.style.display = 'block';
            analogHelp.style.display = 'block';
            
            // Remove min/max restrictions for analog input
            document.getElementById('minThreshold').removeAttribute('min');
            document.getElementById('minThreshold').removeAttribute('max');
            document.getElementById('maxThreshold').removeAttribute('min');
            document.getElementById('maxThreshold').removeAttribute('max');
            break;
        case 'ph_sensor':
            phSensorSettings.style.display = 'block';
            analogInputAlerts.style.display = 'block';
            phThresholdInfo.style.display = 'block';
            phHelp.style.display = 'block';
            
            // Set min/max for pH
            document.getElementById('minThreshold').setAttribute('min', '0');
            document.getElementById('minThreshold').setAttribute('max', '14');
            document.getElementById('maxThreshold').setAttribute('min', '0');
            document.getElementById('maxThreshold').setAttribute('max', '14');
            break;
        case 'dht_temperature':
            analogInputAlerts.style.display = 'block';
            dhtThresholdInfo.style.display = 'block';
            tempHelp.style.display = 'block';
            
            // Set reasonable limits for temperature
            document.getElementById('minThreshold').setAttribute('min', '0');
            document.getElementById('minThreshold').setAttribute('max', '50');
            document.getElementById('maxThreshold').setAttribute('min', '0');
            document.getElementById('maxThreshold').setAttribute('max', '50');
            break;
        case 'dht_humidity':
            analogInputAlerts.style.display = 'block';
            dhtThresholdInfo.style.display = 'block';
            humidHelp.style.display = 'block';
            
            // Set limits for humidity percentage
            document.getElementById('minThreshold').setAttribute('min', '0');
            document.getElementById('minThreshold').setAttribute('max', '100');
            document.getElementById('maxThreshold').setAttribute('min', '0');
            document.getElementById('maxThreshold').setAttribute('max', '100');
            break;
    }

    // Update available pin numbers based on type
    updatePinNumbers();
}

// Call updateSettings on page load
document.addEventListener('DOMContentLoaded', function() {
    updateSettings();
    
    // Setup other event listeners
    const repeatHourly = document.getElementById('repeatHourly');
    const hourlyIntervalSection = document.getElementById('hourlyIntervalSection');
    
    if (repeatHourly) {
        repeatHourly.addEventListener('change', function() {
            hourlyIntervalSection.style.display = this.checked ? 'block' : 'none';
        });
        
        // Trigger change event on load
        repeatHourly.dispatchEvent(new Event('change'));
    }

    // Add event listener for pin type changes
    document.getElementById('type').addEventListener('change', updateSettings);
});

function confirmDelete() {
    if (confirm('Are you sure you want to delete this pin? This action cannot be undone.')) {
        document.getElementById('delete-form').submit();
    }
}

function updateAlertMessage() {
    const select = document.getElementById('messageTemplate');
    const textarea = document.getElementById('alertMessage');
    
    const templates = {
        'basic': '🔔 {device_name}\n{pin_name} is now {status}',
        'detailed': '🔔 Device: {device_name}\nPin: {pin_name}\nStatus: {status}\nValue: {value}\nTime: {time}',
        'schedule': '⏰ Scheduled Update\nDevice: {device_name}\n{pin_name} has been {status}\nTime: {time}\nDate: {date}',
        'threshold': '⚠️ Alert: {pin_name}\nCurrent Value: {value}\nThreshold: {threshold}\nDevice: {device_name}\nTime: {time}',
        'custom': textarea.value // preserve current custom template if any
    };
    
    if (select.value && select.value !== 'custom') {
        textarea.value = templates[select.value];
    }
}

function testTelegramConnection() {
    const chatId = document.getElementById('telegramChatId').value;
    const resultDiv = document.getElementById('telegramTestResult');
    
    if (!chatId) {
        resultDiv.innerHTML = '<div class="alert alert-warning">Please enter a Telegram Chat ID first</div>';
        resultDiv.style.display = 'block';
        return;
    }

    // Show loading state
    resultDiv.innerHTML = '<div class="alert alert-info">Testing connection...</div>';
    resultDiv.style.display = 'block';

    // Make AJAX call to test connection
    fetch(`/telegram/test-connection/${chatId}`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            resultDiv.innerHTML = '<div class="alert alert-success">✅ Connection successful! You should receive a test message on Telegram.</div>';
        } else {
            resultDiv.innerHTML = '<div class="alert alert-danger">❌ Connection failed. Please verify your Chat ID and try again.</div>';
        }
    })
    .catch(error => {
        resultDiv.innerHTML = '<div class="alert alert-danger">❌ Error testing connection. Please try again.</div>';
        console.error('Error:', error);
    });
}

function toggleDefaultValues(checked) {
    const defaultValues = {
        'cal4': 4090,    // pH 4.0
        'cal7': 3140,    // pH 7.0
        'cal10': 2350,   // pH 10.0
        'samples': 10,   // Default samples
        'interval': 1000 // Default interval
    };
    
    Object.entries(defaultValues).forEach(([inputId, value]) => {
        const input = document.getElementById(inputId);
        
        if (checked) {
            // Simpan nilai sebelumnya sebelum menggunakan default
            input.dataset.previousValue = input.value;
            input.value = value;
            input.classList.add('bg-light');
        } else {
            // Kembalikan ke nilai sebelumnya jika ada
            input.value = input.dataset.previousValue || '';
            input.classList.remove('bg-light');
        }
    });
}
</script>
@endpush
@endsection 