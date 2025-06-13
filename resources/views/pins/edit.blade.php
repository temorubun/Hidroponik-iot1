@extends('layouts.dashboard')

@section('content')
    @component('layouts.content-layout')
        @slot('breadcrumb')
            <div class="d-flex align-items-center gap-3">
                <a href="{{ route('devices.index') }}" class="dashboard-link" data-aos="fade-right">
                <i class="fas fa-microchip me-2"></i>
                    Devices
                </a>
                <i class="fas fa-angle-right text-muted"></i>
                <a href="{{ route('devices.show', $pin->device) }}" class="dashboard-link" data-aos="fade-right" data-aos-delay="100">
                    <i class="fas fa-server me-2"></i>
                    {{ $pin->device->name }}
                </a>
            </div>
        @endslot

        @slot('title', 'Edit Pin')
        @slot('subtitle', $pin->name)
        @slot('icon', 'fas fa-plug')

        @include('components.confirm-modal', [
            'title' => 'Delete Pin',
            'message' => 'Are you sure you want to delete this pin? This action cannot be undone.',
            'formId' => 'deletePinForm'
        ])

        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card" data-aos="fade-up" data-aos-delay="100">
                <div class="card-body p-4">
                        <form method="POST" action="{{ route('pins.update', ['device' => $pin->device->uuid, 'pin' => $pin->uuid]) }}" id="pinSettingsForm">
                        @csrf
                        @method('PUT')

                        <!-- Pin Name -->
                            <div class="mb-4" data-aos="fade-up" data-aos-delay="200">
                            <label for="name" class="form-label">
                                <i class="fas fa-tag me-2 gradient-icon"></i><span class="gradient-text">Pin Name</span>
                            </label>
                            <div class="input-group input-group-custom">
                                <span class="input-group-text">
                                        <i class="fas fa-microchip gradient-icon"></i>
                                </span>
                                <input type="text" class="form-control form-control-custom @error('name') is-invalid @enderror" 
                                           id="name" name="name" value="{{ old('name', $pin->name) }}"
                                           placeholder="Enter pin name" required>
                                @error('name')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                            <small class="form-text text-muted mt-2">Choose a descriptive name for your pin</small>
                        </div>

                            <!-- Description -->
                            <div class="mb-4" data-aos="fade-up" data-aos-delay="300">
                                <label for="description" class="form-label">
                                    <i class="fas fa-align-left me-2 gradient-icon"></i><span class="gradient-text">Description</span>
                                </label>
                                <div class="input-group input-group-custom">
                                    <span class="input-group-text">
                                        <i class="fas fa-comment gradient-icon"></i>
                                    </span>
                                    <textarea class="form-control form-control-custom @error('description') is-invalid @enderror"
                                              id="description" name="description" rows="3"
                                              placeholder="Enter pin description">{{ old('description', $pin->description) }}</textarea>
                                    @error('description')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                                <small class="form-text text-muted mt-2">Optional: Add a description for your pin</small>
                        </div>

                        <!-- Pin Type -->
                            <div class="mb-4" data-aos="fade-up" data-aos-delay="400">
                            <label for="type" class="form-label">
                                <i class="fas fa-cog me-2 gradient-icon"></i><span class="gradient-text">Pin Type</span>
                            </label>
                            <div class="input-group input-group-custom">
                                <span class="input-group-text">
                                        <i class="fas fa-bolt gradient-icon"></i>
                                </span>
                                <select class="form-select form-control-custom @error('type') is-invalid @enderror" 
                                            id="type" name="type" required onchange="updatePinNumbers()">
                                    <option value="">Select Pin Type</option>
                                    <option value="digital_output" {{ old('type', $pin->type) == 'digital_output' ? 'selected' : '' }}>
                                        Digital Output (e.g. relay, LED)
                                    </option>
                                    <option value="digital_input" {{ old('type', $pin->type) == 'digital_input' ? 'selected' : '' }}>
                                        Digital Input (e.g. button, switch)
                                    </option>
                                    <option value="analog_input" {{ old('type', $pin->type) == 'analog_input' ? 'selected' : '' }}>
                                            Analog Input (e.g. basic sensors)
                                    </option>
                                    <option value="ph_sensor" {{ old('type', $pin->type) == 'ph_sensor' ? 'selected' : '' }}>
                                        pH Sensor
                                    </option>
                                </select>
                                @error('type')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                                <small class="form-text text-muted mt-2">Choose how this pin will be used</small>
                        </div>

                            <!-- Pin Number -->
                            <div class="mb-4" data-aos="fade-up" data-aos-delay="500" id="pinNumberGroup" style="display: none;">
                            <label for="pin_number" class="form-label">
                                    <i class="fas fa-hashtag me-2 gradient-icon"></i><span class="gradient-text">Pin Number</span>
                            </label>
                            <div class="input-group input-group-custom">
                                <span class="input-group-text">
                                        <i class="fas fa-plug gradient-icon"></i>
                                </span>
                                <select class="form-select form-control-custom @error('pin_number') is-invalid @enderror" 
                                    id="pin_number" name="pin_number" required>
                                        <option value="">Select Pin Number</option>
                                </select>
                                @error('pin_number')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                                <small class="form-text text-muted mt-2">Select the GPIO pin number on your device</small>
                        </div>

                            <!-- pH Sensor Settings -->
                            <div class="mb-4" data-aos="fade-up" data-aos-delay="500" id="phSensorSettings" style="display: none;">
                                <div class="card settings-card">
                                <div class="card-body">
                                        <h6 class="card-title mb-4">
                                            <i class="fas fa-flask me-2"></i><span class="gradient-text">pH Sensor Settings</span>
                                    </h6>

                                        <!-- Calibration Points -->
                                        <div class="mb-4">
                                            <label class="form-label"><span class="gradient-text">Calibration Points</span></label>
                                    <div class="form-check mb-3">
                                                <input type="checkbox" class="form-check-input" id="use_default_values" 
                                                       name="settings[ph_sensor][use_default_values]"
                                                       {{ isset($pin->settings['ph_sensor']['use_default_values']) && $pin->settings['ph_sensor']['use_default_values'] ? 'checked' : '' }}
                                                       onchange="toggleCalibrationInputs(this.checked)">
                                                <label class="form-check-label" for="use_default_values">
                                                    Use Default Values
                                        </label>
                                    </div>

                                            <div id="calibrationInputs">
                                                <!-- pH 4.0 -->
                                                <div class="mb-3">
                                                    <label class="form-label"><span class="gradient-text">pH 4.0</span></label>
                                            <div class="input-group input-group-custom">
                                                <span class="input-group-text">
                                                            <i class="fas fa-tint gradient-icon"></i>
                                                </span>
                                                <input type="number" class="form-control form-control-custom" 
                                                               name="settings[ph_sensor][ph4_value]"
                                                               id="ph4_value"
                                                               placeholder="Raw ADC value"
                                                               value="{{ isset($pin->settings['ph_sensor']['use_default_values']) && $pin->settings['ph_sensor']['use_default_values'] ? '4090' : ($pin->settings['ph_sensor']['ph4_value'] ?? '') }}">
                                            </div>
                                        </div>

                                                <!-- pH 7.0 -->
                                                <div class="mb-3">
                                                    <label class="form-label"><span class="gradient-text">pH 7.0</span></label>
                                            <div class="input-group input-group-custom">
                                                <span class="input-group-text">
                                                            <i class="fas fa-tint gradient-icon"></i>
                                                </span>
                                                <input type="number" class="form-control form-control-custom" 
                                                               name="settings[ph_sensor][ph7_value]"
                                                               id="ph7_value"
                                                               placeholder="Raw ADC value"
                                                               value="{{ isset($pin->settings['ph_sensor']['use_default_values']) && $pin->settings['ph_sensor']['use_default_values'] ? '3140' : ($pin->settings['ph_sensor']['ph7_value'] ?? '') }}">
                                            </div>
                                        </div>

                                                <!-- pH 10.0 -->
                                                <div class="mb-3">
                                                    <label class="form-label"><span class="gradient-text">pH 10.0</span></label>
                                            <div class="input-group input-group-custom">
                                                <span class="input-group-text">
                                                            <i class="fas fa-tint gradient-icon"></i>
                                                </span>
                                                <input type="number" class="form-control form-control-custom" 
                                                               name="settings[ph_sensor][ph10_value]"
                                                               id="ph10_value"
                                                               placeholder="Raw ADC value"
                                                               value="{{ isset($pin->settings['ph_sensor']['use_default_values']) && $pin->settings['ph_sensor']['use_default_values'] ? '2350' : ($pin->settings['ph_sensor']['ph10_value'] ?? '') }}">
                                            </div>
                                        </div>
                                    </div>
                                    </div>

                                        <!-- Reading Settings -->
                                        <div class="mb-3">
                                            <label class="form-label"><span class="gradient-text">Reading Settings</span></label>
                                            <div class="row g-3">
                                                <div class="col-md-6">
                                            <div class="input-group input-group-custom">
                                                <span class="input-group-text">
                                                    <i class="fas fa-layer-group gradient-icon"></i>
                                                </span>
                                                <input type="number" class="form-control form-control-custom" 
                                                               name="settings[ph_sensor][samples]"
                                                               placeholder="Number of samples"
                                                               value="{{ $pin->settings['ph_sensor']['samples'] ?? '10' }}">
                                            </div>
                                                    <small class="form-text text-muted">Number of samples to average (1-100)</small>
                                        </div>
                                                <div class="col-md-6">
                                            <div class="input-group input-group-custom">
                                                <span class="input-group-text">
                                                    <i class="fas fa-clock gradient-icon"></i>
                                                </span>
                                                <input type="number" class="form-control form-control-custom" 
                                                               name="settings[ph_sensor][interval]"
                                                               placeholder="Reading interval"
                                                               value="{{ $pin->settings['ph_sensor']['interval'] ?? '1000' }}">
                                                        <span class="input-group-text bg-light">ms</span>
                                            </div>
                                                    <small class="form-text text-muted">Reading interval (min 100ms)</small>
                                                </div>
                                        </div>
                                    </div>

                                        <!-- Important Note -->
                                        <div class="alert alert-info mt-3 mb-0">
                                        <i class="fas fa-info-circle me-2"></i>
                                            <strong>Important:</strong> For pH sensors, use ADC1 pins (GPIO32-39) for better accuracy. Recommended pins: GPIO36, GPIO39, GPIO34, or GPIO35.
                                    </div>
                                </div>
                            </div>
                        </div>

                            <!-- Additional Settings -->
                            <div class="mt-5" data-aos="fade-up" data-aos-delay="600">
                                <h5 class="gradient-text mb-4">
                                    <i class="fas fa-cogs me-2"></i>Additional Settings
                                </h5>

                                    <!-- Schedule Settings Card -->
                                    <div class="card settings-card mb-4">
                                        <div class="card-body">
                                            <h6 class="card-title mb-4">
                                                        <i class="fas fa-clock me-2"></i><span class="gradient-text">Schedule Settings</span>
                                            </h6>

                                            <!-- Enable Schedule Toggle -->
                                            <div class="form-check mb-3">
                                                <input type="checkbox" class="form-check-input" id="useSchedule" 
                                                        name="settings[schedule][enabled]" 
                                                        {{ isset($pin->settings['schedule']['enabled']) && $pin->settings['schedule']['enabled'] ? 'checked' : '' }}
                                                        onchange="toggleScheduleSettings(this.checked)">
                                                <label class="form-check-label" for="useSchedule">
                                                    Enable Schedule
                                        </label>
                                    </div>

                                    <!-- Schedule Settings Content -->
                                    <div id="scheduleSettingsContent">
                                                <!-- Timing Settings -->
                                        <div class="mb-4">
                                            <label class="form-label">
                                                <span class="gradient-text">
                                                            <i class="fas fa-clock me-2"></i>Timing Settings
                                                        </span>
                                                    </label>
                                                    <div class="card settings-card">
                                                        <div class="card-body">
                                                            <div class="row g-4">
                                                                <!-- Start Time -->
                                                                <div class="col-lg-6">
                                                                    <label class="form-label">
                                                                        <span class="gradient-text">
                                                                            <i class="fas fa-play me-2"></i>Start Time
                                                </span>
                                            </label>
                                            <div class="input-group input-group-custom">
                                                <span class="input-group-text">
                                                                            <i class="fas fa-clock gradient-icon"></i>
                                                </span>
                                                                        <input type="time" class="form-control form-control-custom"
                                                                                id="start_time" 
                                                                                name="settings[schedule][start_time]"
                                                                                value="{{ $pin->settings['schedule']['start_time'] ?? '' }}">
                                            </div>
                                        </div>

                                                                <!-- ON Duration -->
                                                                <div class="col-lg-6">
                                            <label class="form-label">
                                                <span class="gradient-text">
                                                                            <i class="fas fa-hourglass-half me-2"></i>ON Duration
                                                </span>
                                            </label>
                                                                    <div class="input-group input-group-custom">
                                                                        <span class="input-group-text">
                                                                            <i class="fas fa-hourglass-start gradient-icon"></i>
                                                                        </span>
                                                                        <input type="number" class="form-control form-control-custom"
                                                                                id="on_duration" 
                                                                                name="settings[schedule][on_duration]"
                                                                                placeholder="e.g. 5"
                                                                                value="{{ $pin->settings['schedule']['on_duration'] ?? '' }}">
                                                                        <span class="input-group-text">minutes</span>
                                                        </div>
                                                                    <small class="form-text text-muted">How long the pin stays ON in each cycle</small>
                                        </div>

                                                                <!-- Cycle Interval -->
                                                                <div class="col-lg-6">
                                            <label class="form-label">
                                                <span class="gradient-text">
                                                                            <i class="fas fa-sync me-2"></i>Cycle Interval
                                                </span>
                                            </label>
                                                                    <div class="input-group input-group-custom">
                                                                        <span class="input-group-text">
                                                                            <i class="fas fa-sync gradient-icon"></i>
                                                                        </span>
                                                                        <input type="number" class="form-control form-control-custom"
                                                                                id="cycle_interval" 
                                                                                name="settings[schedule][cycle_interval]"
                                                                                placeholder="e.g. 10"
                                                                                value="{{ $pin->settings['schedule']['cycle_interval'] ?? '' }}">
                                                                        <span class="input-group-text">minutes</span>
                                                        </div>
                                                                    <small class="form-text text-muted">Total time for one ON/OFF cycle</small>
                                        </div>

                                                                <!-- Cycle Duration -->
                                                        <div class="col-lg-6">
                                                            <label class="form-label">
                                                                <span class="gradient-text">
                                                                            <i class="fas fa-stopwatch me-2"></i>Cycle Duration
                                                                </span>
                                                            </label>
                                                            <div class="input-group input-group-custom">
                                                                <span class="input-group-text">
                                                                            <i class="fas fa-stopwatch gradient-icon"></i>
                                                                </span>
                                                                        <input type="number" class="form-control form-control-custom"
                                                                                id="cycle_duration" 
                                                                                name="settings[schedule][cycle_duration]"
                                                                                placeholder="e.g. 30"
                                                                                value="{{ $pin->settings['schedule']['cycle_duration'] ?? '' }}">
                                                                        <span class="input-group-text">minutes</span>
                                                            </div>
                                                                    <small class="form-text text-muted">How long the cycles should continue</small>
                                                            </div>
                                                        </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Repeat Settings -->
                                                <div class="mb-4">
                                                            <label class="form-label">
                                                                <span class="gradient-text">
                                                            <i class="fas fa-redo me-2"></i>Repeat Settings
                                                                </span>
                                                            </label>
                                                    <div class="card settings-card">
                                                        <div class="card-body">
                                                            <div class="form-check mb-3">
                                                                <input type="checkbox" class="form-check-input" id="repeatHourly" 
                                                                        name="settings[schedule][repeat_hourly]" value="1"
                                                                        {{ isset($pin->settings['schedule']['repeat_hourly']) && $pin->settings['schedule']['repeat_hourly'] ? 'checked' : '' }}
                                                                        onchange="toggleRepeatSettings(this.checked)">
                                                                <label class="form-check-label" for="repeatHourly">
                                                                    Enable Schedule Repeat
                                                                </label>
                                                            </div>

                                                            <div id="repeatSettingsContent">
                                                            <div class="input-group input-group-custom">
                                                                <span class="input-group-text">
                                                                        <i class="fas fa-redo gradient-icon"></i>
                                                                </span>
                                                                    <input type="number" class="form-control form-control-custom" 
                                                                            id="hourlyInterval" name="settings[schedule][hourly_interval]"
                                                                            value="{{ $pin->settings['schedule']['hourly_interval'] ?? '' }}"
                                                                            placeholder="e.g. 180 for 3 hours">
                                                                    <span class="input-group-text">minutes</span>
                                                            </div>
                                                                <small class="form-text text-muted">Enter how often the schedule should repeat</small>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    </div>

                                                <!-- Schedule Info -->
                                                <div class="alert alert-info mt-4">
                                                        <i class="fas fa-info-circle me-2"></i>
                                                    <strong>Schedule Guide:</strong><br>
                                                    - Set start time when the schedule begins<br>
                                                    - ON Duration: How long the pin stays ON<br>
                                                    - Cycle Interval: Total time for one ON/OFF cycle<br>
                                                    - Cycle Duration: Total running time of all cycles
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                    <!-- Alert Settings -->
                                    <div class="card settings-card mb-4">
                                        <div class="card-body">
                                            <h6 class="card-title mb-4">Alert Settings</h6>
                                            
                                            <!-- Telegram Alert Settings -->
                                            <div class="mb-4">
                                                <label class="form-label d-block">Telegram Alert Settings</label>
                                                <div class="form-check mb-3">
                                                    <input type="checkbox" class="form-check-input" id="enable_telegram" 
                                                           name="settings[alerts][enabled]" 
                                                           {{ isset($pin->settings['alerts']['enabled']) && $pin->settings['alerts']['enabled'] ? 'checked' : '' }}
                                                           onchange="toggleTelegramSettings(this.checked)">
                                                    <label class="form-check-label" for="enable_telegram">
                                                        Enable Telegram Alerts
                                            </label>
                                                </div>

                                                <div id="telegramSettings" class="{{ isset($pin->settings['alerts']['enabled']) && $pin->settings['alerts']['enabled'] ? '' : 'd-none' }}">
                                                    <!-- Telegram Chat ID -->
                                                    <div class="mb-3">
                                                        <label for="telegram_chat_id" class="form-label">Telegram Chat ID</label>
                                                        <div class="input-group input-group-custom">
                                                            <span class="input-group-text">
                                                                <i class="fab fa-telegram gradient-icon"></i>
                                                </span>
                                                            <input type="text" class="form-control form-control-custom"
                                                                   id="telegram_chat_id" 
                                                                   name="settings[alerts][telegram_chat_id]"
                                                                   value="{{ $pin->settings['alerts']['telegram_chat_id'] ?? '' }}"
                                                                   placeholder="e.g. 123456789">
                                                            <button type="button" class="btn btn-outline-primary" onclick="testTelegramConnection()">
                                                                Test Connection
                                                            </button>
                                                        </div>
                                                        <small class="form-text text-muted">This is required to receive alerts on Telegram</small>
                                                    </div>

                                                    <!-- Alert on State Change -->
                                                    <div class="mb-3">
                                                        <label class="form-label d-block">Alert on State Change</label>
                                                        <div class="form-check mb-2">
                                                            <input type="checkbox" class="form-check-input" 
                                                                   id="alert_on_high" 
                                                                   name="settings[alerts][on_high]"
                                                                   {{ isset($pin->settings['alerts']['on_high']) && $pin->settings['alerts']['on_high'] ? 'checked' : '' }}>
                                                            <label class="form-check-label" for="alert_on_high">
                                                                Alert when turned ON
                                            </label>
                                                        </div>
                                                        <div class="form-check">
                                                            <input type="checkbox" class="form-check-input" 
                                                                   id="alert_on_low" 
                                                                   name="settings[alerts][on_low]"
                                                                   {{ isset($pin->settings['alerts']['on_low']) && $pin->settings['alerts']['on_low'] ? 'checked' : '' }}>
                                                            <label class="form-check-label" for="alert_on_low">
                                                                Alert when turned OFF
                                                            </label>
                                            </div>
                                        </div>

                                        <!-- Message Template -->
                                                    <div class="mb-3">
                                                        <label for="message_template" class="form-label">Message Template</label>
                                                        <div class="mb-2">
                                                            <select class="form-select form-control-custom" id="template_selector" onchange="updateMessageTemplate(this.value)">
                                                            <option value="">Select Template</option>
                                                                <option value="basic_status">Basic Status</option>
                                                                <option value="detailed_status">Detailed Status</option>
                                                                <option value="schedule_status">Schedule Status</option>
                                                                <option value="threshold_alert">Threshold Alert</option>
                                                            <option value="custom">Custom Template</option>
                                                        </select>
                                                    </div>
                                                    <div class="input-group input-group-custom">
                                                        <span class="input-group-text">
                                                                <i class="fas fa-envelope gradient-icon"></i>
                                                        </span>
                                                            <textarea class="form-control form-control-custom"
                                                                      id="message_template" 
                                                                      name="settings[alerts][message_template]"
                                                                      rows="3"
                                                                      placeholder="e.g. {device_name} - {pin_name} is now {status}">{{ $pin->settings['alerts']['message_template'] ?? '{device_name} - {pin_name} is now {status}' }}</textarea>
                                                    </div>
                                                        <small class="form-text text-muted">
                                                        Available variables: {device_name}, {pin_name}, {status}, {value}, {threshold}, {time}, {date}
                                                    </small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                        </div>

                            <!-- Submit Button -->
                            <div class="d-flex gap-2 mt-5" data-aos="fade-up" data-aos-delay="700">
                                <button type="submit" class="btn btn-primary flex-grow-1" onclick="submitForm(event)">
                                    <i class="fas fa-save me-2"></i>Update Pin
                                </button>
                                <a href="{{ route('devices.show', $pin->device) }}" class="btn btn-light">
                                    <i class="fas fa-times me-2"></i>Cancel
                                </a>
                            </div>
                        </form>

                        <!-- Delete Pin -->
                        <div class="mt-5 pt-4 border-top" data-aos="fade-up" data-aos-delay="800">
                            <h5 class="text-danger mb-4">
                                <i class="fas fa-exclamation-triangle me-2"></i>Danger Zone
                            </h5>
                            <form method="POST" action="{{ route('pins.destroy', ['device' => $pin->device->uuid, 'pin' => $pin->uuid]) }}" 
                                  id="deletePinForm">
                                @csrf
                                @method('DELETE')
                                <button type="button" class="btn btn-outline-danger" onclick="confirmDelete('deletePinForm')">
                                    <i class="fas fa-trash-alt me-2"></i>Delete Pin
                                </button>
                            </form>
                            </div>
                        </div>
                </div>
            </div>
        </div>
    @endcomponent
@endsection

@push('styles')
<link href="{{ asset('css/projects.css') }}" rel="stylesheet">
<style>
.gradient-icon {
    background: var(--primary-gradient);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}

.input-group-custom {
    border-radius: 12px;
    border: 1px solid rgba(0, 191, 166, 0.1);
    transition: all 0.3s ease;
    background: white;
}

.input-group-custom:focus-within {
    border-color: var(--primary);
    box-shadow: 0 0 0 4px rgba(0, 191, 166, 0.1);
}

.form-control-custom {
    border: none;
    padding: 0.75rem 1rem;
}

.form-control-custom:focus {
    box-shadow: none;
}

.input-group-text {
    background: transparent;
    border: none;
    padding-left: 1.25rem;
}

.form-label {
    font-weight: 600;
    color: var(--primary);
    margin-bottom: 0.5rem;
}

.form-text {
    color: #6c757d;
}

.settings-card {
    border: 1px solid rgba(0, 191, 166, 0.1);
    border-radius: 12px;
    background: rgba(255, 255, 255, 0.5);
    backdrop-filter: blur(10px);
}

.settings-card .card-title {
    color: var(--primary);
    font-weight: 600;
}

.form-check-input:checked {
    background-color: var(--primary);
    border-color: var(--primary);
}

.form-check-input:focus {
    border-color: var(--primary);
    box-shadow: 0 0 0 0.25rem rgba(0, 191, 166, 0.25);
}

/* Animasi untuk Settings Content */
#scheduleSettingsContent {
    max-height: 0;
    overflow: hidden;
    opacity: 0;
    transition: max-height 0.5s ease-in-out, opacity 0.3s ease-in-out, margin 0.3s ease-in-out;
}

#scheduleSettingsContent.show {
    max-height: 2000px; /* Sesuaikan dengan tinggi maksimum konten */
    opacity: 1;
    margin-top: 1rem;
}

#repeatSettingsContent {
    max-height: 0;
    overflow: hidden;
    opacity: 0;
    transition: max-height 0.5s ease-in-out, opacity 0.3s ease-in-out, margin 0.3s ease-in-out;
}

#repeatSettingsContent.show {
    max-height: 200px;
    opacity: 1;
    margin-top: 1rem;
}

.fade-scale {
    transform: scale(0.95);
    opacity: 0;
    transition: transform 0.3s ease, opacity 0.3s ease;
}

.fade-scale.show {
    transform: scale(1);
    opacity: 1;
}
</style>
@endpush

@push('scripts')
<script>
const pinCapabilities = @json(App\Models\EspPin::getAvailablePins());
const currentPinNumber = {{ $pin->pin_number }};

function updatePinNumbers() {
    const typeSelect = document.getElementById('type');
    const pinNumberSelect = document.getElementById('pin_number');
    const pinNumberGroup = document.getElementById('pinNumberGroup');
    const phSensorSettings = document.getElementById('phSensorSettings');
    const selectedType = typeSelect.value;
    
    // Clear current options
    pinNumberSelect.innerHTML = '<option value="">Select Pin Number</option>';

    // Hide pin number select if no type is selected
    if (!selectedType) {
        pinNumberGroup.style.display = 'none';
        phSensorSettings.style.display = 'none';
        return;
    }

    // Show pin number select
    pinNumberGroup.style.display = 'block';

    // Show/hide pH sensor settings
    phSensorSettings.style.display = selectedType === 'ph_sensor' ? 'block' : 'none';

    // Map ph_sensor to analog_input since they use the same pins
    const capability = selectedType === 'ph_sensor' ? 'analog_input' : selectedType;

    // Filter pins based on capability
    const availablePins = pinCapabilities.filter(pin => 
        pin.capabilities.includes(capability)
    );

    // Add filtered pins to select
        availablePins.forEach(pin => {
                const option = document.createElement('option');
        option.value = pin.number;
        option.textContent = `GPIO${pin.number}`;
        if (pin.number == currentPinNumber) {
            option.selected = true;
        }
        pinNumberSelect.appendChild(option);
    });
}

function toggleCalibrationInputs(useDefault) {
    const ph4Input = document.getElementById('ph4_value');
    const ph7Input = document.getElementById('ph7_value');
    const ph10Input = document.getElementById('ph10_value');

    // Default values
    const defaultValues = {
        ph4: 4090,
        ph7: 3140,
        ph10: 2350
    };

    if (useDefault) {
        // Set default values when checkbox is checked
        ph4Input.value = defaultValues.ph4;
        ph7Input.value = defaultValues.ph7;
        ph10Input.value = defaultValues.ph10;
    } else {
        // Clear values when checkbox is unchecked
        ph4Input.value = '';
        ph7Input.value = '';
        ph10Input.value = '';
    }
}

// Run on page load to handle initial values
document.addEventListener('DOMContentLoaded', function() {
    updatePinNumbers();
    
    // Initialize pH sensor settings if needed
    const typeSelect = document.getElementById('type');
    if (typeSelect.value === 'ph_sensor') {
        document.getElementById('phSensorSettings').style.display = 'block';
    }
});

function toggleTelegramSettings(enabled) {
    const telegramSettings = document.getElementById('telegramSettings');
    if (enabled) {
        telegramSettings.classList.remove('d-none');
    } else {
        telegramSettings.classList.add('d-none');
    }
}

function testTelegramConnection() {
    const chatId = document.getElementById('telegram_chat_id').value;
    if (!chatId) {
        notifications.warning('Please enter a Telegram Chat ID first');
        return;
    }

    const button = event.target;
    const originalText = button.innerHTML;
    button.disabled = true;
    button.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Testing...';

    axios.post('/telegram/test', { chat_id: chatId })
        .then(response => {
            notifications.success('Test message sent successfully!');
        })
        .catch(error => {
            if (error.response && error.response.data && error.response.data.message) {
                notifications.error('Failed to send test message. Please check your Chat ID.');
        } else {
                notifications.error('Error testing connection. Please try again.');
            }
        })
        .finally(() => {
            button.disabled = false;
            button.innerHTML = originalText;
        });
}

function updateMessageTemplate(templateType) {
    const messageTemplate = document.getElementById('message_template');
    
    const templates = {
        'basic_status': '{device_name} - {pin_name} is now {status}',
        'detailed_status': '🔔 Device: {device_name}\n📍 Pin: {pin_name}\n📊 Status: {status}\n📈 Value: {value}\n⏰ Time: {time}',
        'schedule_status': '⏰ Scheduled Action\nDevice: {device_name}\nPin: {pin_name}\nAction: {status}\nTime: {time}\nDate: {date}',
        'threshold_alert': '⚠️ ALERT!\nDevice: {device_name}\nPin: {pin_name}\nValue: {value}\nThreshold: {threshold}\nTime: {time}',
        'custom': messageTemplate.value // Preserve current value when switching to custom
    };

    if (templateType && templateType !== 'custom') {
        messageTemplate.value = templates[templateType];
    }
}

function toggleScheduleSettings(enabled) {
    const scheduleSettingsContent = document.getElementById('scheduleSettingsContent');
    if (enabled) {
        scheduleSettingsContent.classList.add('show');
        } else {
        scheduleSettingsContent.classList.remove('show');
        // Reset repeat settings when schedule is disabled
        document.getElementById('repeatHourly').checked = false;
        toggleRepeatSettings(false);
    }
}

function toggleRepeatSettings(enabled) {
    const repeatSettingsContent = document.getElementById('repeatSettingsContent');
    if (enabled) {
        repeatSettingsContent.classList.add('show');
    } else {
        repeatSettingsContent.classList.remove('show');
    }
}

// Initialize settings visibility on page load
document.addEventListener('DOMContentLoaded', function() {
    const scheduleEnabled = document.getElementById('useSchedule').checked;
    const repeatEnabled = document.getElementById('repeatHourly').checked;
    
    toggleScheduleSettings(scheduleEnabled);
    toggleRepeatSettings(repeatEnabled);
});

function submitForm(event) {
    event.preventDefault();
    
    // Get the form
    const form = document.getElementById('pinSettingsForm');
    
    // Basic validation
    const scheduleEnabled = document.getElementById('useSchedule').checked;
    if (scheduleEnabled) {
        const startTime = document.getElementById('start_time').value;
        const onDuration = document.getElementById('on_duration').value;
        const cycleInterval = document.getElementById('cycle_interval').value;
        
        if (!startTime) {
            notifications.warning('Please set a start time for the schedule');
            return;
        }
        if (!onDuration) {
            notifications.warning('Please set the ON duration');
            return;
        }
        if (!cycleInterval) {
            notifications.warning('Please set the cycle interval');
            return;
        }
    }

    // Show loading state on button
    const submitBtn = event.target;
    const originalText = submitBtn.innerHTML;
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Updating...';

    // Submit the form
    form.submit();
}

function confirmDeletePin() {
    confirmDelete('deletePinForm');
}
</script>
@endpush