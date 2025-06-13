@extends('layouts.dashboard')

@section('content')
    @component('layouts.content-layout')
        @slot('breadcrumb')
            <a href="{{ route('devices.show', $device) }}" class="dashboard-link">
                <i class="fas fa-microchip me-2"></i>
                {{ $device->name }}
            </a>
        @endslot

        @slot('title', 'Add New Pin')
        @slot('subtitle', 'Configure a new pin for your IoT device')
        @slot('icon', 'fas fa-plug')

        <div class="col-lg-8 mx-auto">
            <div class="project-card" data-aos="fade-up" data-aos-duration="1000">
                <div class="card-body p-4">
                    <form method="POST" action="{{ route('pins.store', $device) }}" id="createPinForm" class="form-centered">
                        @csrf

                        <!-- Pin Name -->
                        <div class="form-group mb-4" data-aos="fade-up" data-aos-delay="100">
                            <label for="name" class="form-label">
                                <i class="fas fa-tag me-2 gradient-icon"></i><span class="gradient-text">Pin Name</span>
                            </label>
                            <div class="input-group input-group-custom">
                                <span class="input-group-text">
                                    <i class="fas fa-tag gradient-icon"></i>
                                </span>
                                <input type="text" class="form-control form-control-custom @error('name') is-invalid @enderror" 
                                    id="name" name="name" value="{{ old('name') }}" required 
                                    placeholder="Enter pin name">
                                @error('name')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                            <small class="form-text text-muted mt-2">Choose a descriptive name for your pin</small>
                        </div>

                        <!-- Pin Type -->
                        <div class="form-group mb-4" data-aos="fade-up" data-aos-delay="200">
                            <label for="type" class="form-label">
                                <i class="fas fa-cog me-2 gradient-icon"></i><span class="gradient-text">Pin Type</span>
                            </label>
                            <div class="input-group input-group-custom">
                                <span class="input-group-text">
                                    <i class="fas fa-cog gradient-icon"></i>
                                </span>
                                <select class="form-select form-control-custom @error('type') is-invalid @enderror" 
                                    id="type" name="type" required onchange="updatePinNumbers()">
                                    <option value="">Select Pin Type</option>
                                    <option value="digital_output" {{ old('type') == 'digital_output' ? 'selected' : '' }}>
                                        Digital Output (e.g. relay, LED)
                                    </option>
                                    <option value="digital_input" {{ old('type') == 'digital_input' ? 'selected' : '' }}>
                                        Digital Input (e.g. button, switch)
                                    </option>
                                    <option value="analog_input" {{ old('type') == 'analog_input' ? 'selected' : '' }}>
                                        Analog Input (e.g. basic sensors)
                                    </option>
                                    <option value="ph_sensor" {{ old('type') == 'ph_sensor' ? 'selected' : '' }}>
                                        pH Sensor
                                    </option>
                                </select>
                                @error('type')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                            <small class="form-text text-muted mt-2">Select the type of pin you want to configure</small>
                        </div>

                        <!-- GPIO Pin Number -->
                        <div class="form-group mb-4" data-aos="fade-up" data-aos-delay="300">
                            <label for="pin_number" class="form-label">
                                <i class="fas fa-microchip me-2 gradient-icon"></i><span class="gradient-text">GPIO Pin Number</span>
                            </label>
                            <div class="input-group input-group-custom">
                                <span class="input-group-text">
                                    <i class="fas fa-microchip gradient-icon"></i>
                                </span>
                                <select class="form-select form-control-custom @error('pin_number') is-invalid @enderror" 
                                    id="pin_number" name="pin_number" required>
                                    <option value="">Select GPIO Pin</option>
                                </select>
                                @error('pin_number')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                            <small class="form-text text-muted mt-2">Choose the GPIO pin number for your device</small>
                        </div>

                        <!-- pH Sensor Settings -->
                        <div id="phSensorSettings" style="display: none;" data-aos="fade-up" data-aos-delay="400">
                            <div class="card mb-4">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0 gradient-text">
                                        <i class="fas fa-flask me-2"></i>pH Sensor Settings
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div class="mb-4">
                                        <label class="form-label gradient-text">Calibration Points</label>
                                        <div class="form-check mb-3">
                                            <input class="form-check-input" type="checkbox" id="useDefaultValues" 
                                                onchange="toggleDefaultValues(this.checked)">
                                            <label class="form-check-label" for="useDefaultValues">
                                                Use Default Values
                                            </label>
                                        </div>
                                        <div class="row g-4">
                                            <div class="col-lg-4">
                                                <label class="form-label text-muted small mb-2">pH 4.0 Calibration</label>
                                                <div class="input-group input-group-custom">
                                                    <span class="input-group-text">
                                                        <i class="fas fa-vial gradient-icon"></i>
                                                    </span>
                                                    <span class="input-group-text bg-light">pH 4.0</span>
                                                    <input type="number" class="form-control form-control-custom" 
                                                        id="cal4" name="settings[calibration][4]" 
                                                        placeholder="Raw ADC value" step="1" min="0" max="4095">
                                                </div>
                                            </div>
                                            <div class="col-lg-4">
                                                <label class="form-label text-muted small mb-2">pH 7.0 Calibration</label>
                                                <div class="input-group input-group-custom">
                                                    <span class="input-group-text">
                                                        <i class="fas fa-vial gradient-icon"></i>
                                                    </span>
                                                    <span class="input-group-text bg-light">pH 7.0</span>
                                                    <input type="number" class="form-control form-control-custom" 
                                                        id="cal7" name="settings[calibration][7]" 
                                                        placeholder="Raw ADC value" step="1" min="0" max="4095">
                                                </div>
                                            </div>
                                            <div class="col-lg-4">
                                                <label class="form-label text-muted small mb-2">pH 10.0 Calibration</label>
                                                <div class="input-group input-group-custom">
                                                    <span class="input-group-text">
                                                        <i class="fas fa-vial gradient-icon"></i>
                                                    </span>
                                                    <span class="input-group-text bg-light">pH 10.0</span>
                                                    <input type="number" class="form-control form-control-custom" 
                                                        id="cal10" name="settings[calibration][10]" 
                                                        placeholder="Raw ADC value" step="1" min="0" max="4095">
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <div class="input-group input-group-custom">
                                                <span class="input-group-text">
                                                    <i class="fas fa-layer-group"></i>
                                                </span>
                                                <input type="number" class="form-control form-control-custom" 
                                                    id="samples" name="settings[samples]" 
                                                    value="10" min="1" max="100" step="1" required>
                                            </div>
                                            <small class="text-muted">Number of samples to average (1-100)</small>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="input-group input-group-custom">
                                                <span class="input-group-text">
                                                    <i class="fas fa-clock"></i>
                                                </span>
                                                <input type="number" class="form-control form-control-custom" 
                                                    id="interval" name="settings[interval]" 
                                                    value="1000" min="100" step="100" required>
                                                <span class="input-group-text">ms</span>
                                            </div>
                                            <small class="text-muted">Reading interval (min 100ms)</small>
                                        </div>
                                    </div>

                                    <div class="alert alert-info mt-3">
                                        <i class="fas fa-info-circle me-2"></i>
                                        <strong>Important:</strong> For pH sensors, use ADC1 pins (GPIO32-39) for better accuracy.
                                        Recommended pins: GPIO36, GPIO39, GPIO34, or GPIO35.
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-actions d-flex justify-content-between align-items-center mt-5" data-aos="fade-up" data-aos-delay="500">
                            <a href="{{ route('devices.show', $device) }}" class="btn btn-light">
                                <i class="fas fa-arrow-left me-2"></i>Back
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Add Pin
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endcomponent
@endsection

@push('styles')
<link href="{{ asset('css/projects.css') }}" rel="stylesheet">
@endpush

@push('scripts')
<script>
// Store used pin numbers for the current device
const usedPins = @json($device->pins->pluck('pin_number')->toArray());

// Define available pins for each type
const pinTypes = {
    digital_output: [2, 4, 5, 12, 13, 14, 15, 16, 17, 18, 19, 21, 22, 23, 25, 26, 27, 32, 33],
    digital_input: [2, 4, 5, 12, 13, 14, 15, 16, 17, 18, 19, 21, 22, 23, 25, 26, 27, 32, 33, 34, 35, 36, 39],
    analog_input: [32, 33, 34, 35, 36, 39],
    ph_sensor: [32, 33, 34, 35, 36, 39]
};

function updatePinNumbers() {
    const type = document.getElementById('type').value;
    const pinSelect = document.getElementById('pin_number');
    const phSettings = document.getElementById('phSensorSettings');
    
    // Clear current options
    pinSelect.innerHTML = '<option value="">Select GPIO Pin</option>';
    
    if (type) {
        // Get available pins for selected type
        const availablePins = pinTypes[type];
        
        // Add options for available pins that are not in use
        availablePins.forEach(pin => {
            if (!usedPins.includes(pin)) {
                const option = document.createElement('option');
                option.value = pin;
                option.textContent = `GPIO${pin}${pin >= 34 ? ' (Input Only)' : ''}`;
                pinSelect.appendChild(option);
            }
        });
    }
    
    // Show/hide pH sensor settings
    phSettings.style.display = type === 'ph_sensor' ? 'block' : 'none';
}

// Initialize pin numbers on page load
document.addEventListener('DOMContentLoaded', updatePinNumbers);

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
        if (input) {
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
        }
    });
}
</script>
@endpush