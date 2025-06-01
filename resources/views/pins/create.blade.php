@extends('layouts.app')

@section('content')
<div class="container">
    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-circle me-2"></i>
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fas fa-plus-circle"></i> Add New Pin</h5>
                        <a href="{{ route('devices.show', $device) }}" class="btn btn-light btn-sm">
                            <i class="fas fa-arrow-left"></i> Back
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    <form method="POST" action="{{ route('pins.store', $device) }}" id="createPinForm">
                        @csrf

                        <div class="mb-3">
                            <label for="name" class="form-label">Pin Name</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas fa-tag"></i>
                                </span>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                    id="name" name="name" value="{{ old('name') }}" required 
                                    placeholder="Enter pin name">
                            </div>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="type" class="form-label">Pin Type</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas fa-cog"></i>
                                </span>
                                <select class="form-select @error('type') is-invalid @enderror" 
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
                            </div>
                            @error('type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="pin_number" class="form-label">GPIO Pin Number</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas fa-microchip"></i>
                                </span>
                                <select class="form-select @error('pin_number') is-invalid @enderror" 
                                    id="pin_number" name="pin_number" required>
                                    <option value="">Select GPIO Pin</option>
                                </select>
                            </div>
                            @error('pin_number')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- pH Sensor Settings -->
                        <div id="phSensorSettings" style="display: none;">
                            <div class="card mb-3">
                                <div class="card-header">
                                    <h6 class="mb-0">pH Sensor Settings</h6>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label class="form-label">Calibration Points</label>
                                        <div class="form-check mb-2">
                                            <input class="form-check-input" type="checkbox" id="useDefaultValues" 
                                                onchange="toggleDefaultValues(this.checked)">
                                            <label class="form-check-label" for="useDefaultValues">
                                                Use Default Values
                                            </label>
                                        </div>
                                        <div class="row g-2">
                                            <div class="col-md-4">
                                                <div class="input-group">
                                                    <span class="input-group-text">pH 4.0</span>
                                                    <input type="number" class="form-control" id="cal4" name="settings[calibration][4]" 
                                                        placeholder="Raw ADC value" step="1" min="0" max="4095">
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="input-group">
                                                    <span class="input-group-text">pH 7.0</span>
                                                    <input type="number" class="form-control" id="cal7" name="settings[calibration][7]" 
                                                        placeholder="Raw ADC value" step="1" min="0" max="4095">
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="input-group">
                                                    <span class="input-group-text">pH 10.0</span>
                                                    <input type="number" class="form-control" id="cal10" name="settings[calibration][10]" 
                                                        placeholder="Raw ADC value" step="1" min="0" max="4095">
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row g-2">
                                        <div class="col-md-6">
                                            <div class="input-group">
                                                <span class="input-group-text">Samples</span>
                                                <input type="number" class="form-control" id="samples" name="settings[samples]" 
                                                    value="10" min="1" max="100" step="1" required>
                                            </div>
                                            <small class="text-muted">Number of samples to average (1-100)</small>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="input-group">
                                                <span class="input-group-text">Interval</span>
                                                <input type="number" class="form-control" id="interval" name="settings[interval]" 
                                                    value="1000" min="100" step="100" required>
                                                <span class="input-group-text">ms</span>
                                            </div>
                                            <small class="text-muted">Reading interval (min 100ms)</small>
                                        </div>
                                    </div>

                                    <div class="alert alert-info mt-3">
                                        <i class="fas fa-info-circle"></i>
                                        <strong>Important:</strong> For pH sensors, use ADC1 pins (GPIO32-39) for better accuracy.
                                        Recommended pins: GPIO36, GPIO39, GPIO34, or GPIO35.
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary" id="submitBtn">
                                <i class="fas fa-plus-circle"></i> Add Pin
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
.card {
    border-radius: 15px;
}
.card-header {
    border-top-left-radius: 15px;
    border-top-right-radius: 15px;
}
.input-group-text {
    background-color: #f8f9fa;
    border-right: none;
}
.form-control, .form-select {
    border-left: none;
}
.form-control:focus, .form-select:focus {
    border-color: #ced4da;
    box-shadow: none;
}
.input-group:focus-within .input-group-text {
    border-color: #86b7fe;
}
</style>
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
@endsection 