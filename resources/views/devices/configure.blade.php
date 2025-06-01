@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-microchip"></i> Configure {{ $device->name }}
                        </h5>
                        <div>
                            <button class="btn btn-light btn-sm" onclick="saveConfiguration()">
                                <i class="fas fa-save"></i> Save Configuration
                            </button>
                            <a href="{{ route('devices.show', $device) }}" class="btn btn-light btn-sm ms-2">
                                <i class="fas fa-arrow-left"></i> Back
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <h6><i class="fas fa-info-circle"></i> ESP32 Pin Configuration</h6>
                        <p class="mb-0">Configure each GPIO pin's function. Available functions depend on the pin's capabilities.</p>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="card mb-3">
                                <div class="card-header">
                                    <h6 class="mb-0">Connection Settings</h6>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label class="form-label">WiFi SSID</label>
                                        <div class="input-group">
                                            <span class="input-group-text">
                                                <i class="fas fa-wifi"></i>
                                            </span>
                                            <input type="text" class="form-control" id="wifi_ssid" 
                                                value="{{ $device->wifi_ssid }}" placeholder="Enter WiFi SSID">
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">WiFi Password</label>
                                        <div class="input-group">
                                            <span class="input-group-text">
                                                <i class="fas fa-key"></i>
                                            </span>
                                            <input type="password" class="form-control" id="wifi_password" 
                                                value="{{ $device->wifi_password }}" placeholder="Enter WiFi Password">
                                            <button class="btn btn-outline-secondary" type="button" onclick="togglePassword()">
                                                <i class="fas fa-eye" id="password-toggle-icon"></i>
                                            </button>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Communication Protocol</label>
                                        <div class="input-group">
                                            <span class="input-group-text">
                                                <i class="fas fa-exchange-alt"></i>
                                            </span>
                                            <select class="form-select" id="protocol" onchange="updateProtocolConfig()">
                                                <option value="mqtt">MQTT</option>
                                                <option value="http">HTTP REST API</option>
                                                <option value="websocket">WebSocket</option>
                                                <option value="firebase">Firebase</option>
                                                <option value="blynk">Blynk IoT</option>
                                            </select>
                                        </div>
                                    </div>

                                    <!-- MQTT Configuration -->
                                    <div id="mqtt-config">
                                        <div class="mb-3">
                                            <label class="form-label">MQTT Server</label>
                                            <div class="input-group">
                                                <span class="input-group-text">
                                                    <i class="fas fa-server"></i>
                                                </span>
                                                <input type="text" class="form-control" id="mqtt_server" 
                                                    placeholder="mqtt://example.com">
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">MQTT Username</label>
                                            <div class="input-group">
                                                <span class="input-group-text">
                                                    <i class="fas fa-user"></i>
                                                </span>
                                                <input type="text" class="form-control" id="mqtt_username" 
                                                    placeholder="MQTT username">
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">MQTT Password</label>
                                            <div class="input-group">
                                                <span class="input-group-text">
                                                    <i class="fas fa-key"></i>
                                                </span>
                                                <input type="password" class="form-control" id="mqtt_password" 
                                                    placeholder="MQTT password">
                                            </div>
                                        </div>
                                    </div>

                                    <!-- HTTP Configuration -->
                                    <div id="http-config" style="display: none;">
                                        <div class="mb-3">
                                            <label class="form-label">API Endpoint</label>
                                            <div class="input-group">
                                                <span class="input-group-text">
                                                    <i class="fas fa-globe"></i>
                                                </span>
                                                <input type="text" class="form-control" id="http_endpoint" 
                                                    placeholder="https://api.example.com">
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">API Key</label>
                                            <div class="input-group">
                                                <span class="input-group-text">
                                                    <i class="fas fa-key"></i>
                                                </span>
                                                <input type="text" class="form-control" id="http_api_key" 
                                                    placeholder="Your API key">
                                            </div>
                                        </div>
                                    </div>

                                    <!-- WebSocket Configuration -->
                                    <div id="websocket-config" style="display: none;">
                                        <div class="mb-3">
                                            <label class="form-label">WebSocket Server</label>
                                            <div class="input-group">
                                                <span class="input-group-text">
                                                    <i class="fas fa-plug"></i>
                                                </span>
                                                <input type="text" class="form-control" id="ws_server" 
                                                    placeholder="ws://example.com">
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Authentication Token</label>
                                            <div class="input-group">
                                                <span class="input-group-text">
                                                    <i class="fas fa-key"></i>
                                                </span>
                                                <input type="text" class="form-control" id="ws_token" 
                                                    placeholder="WebSocket auth token">
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Firebase Configuration -->
                                    <div id="firebase-config" style="display: none;">
                                        <div class="mb-3">
                                            <label class="form-label">Firebase Project ID</label>
                                            <div class="input-group">
                                                <span class="input-group-text">
                                                    <i class="fas fa-project-diagram"></i>
                                                </span>
                                                <input type="text" class="form-control" id="firebase_project_id" 
                                                    placeholder="your-project-id">
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Firebase API Key</label>
                                            <div class="input-group">
                                                <span class="input-group-text">
                                                    <i class="fas fa-key"></i>
                                                </span>
                                                <input type="text" class="form-control" id="firebase_api_key" 
                                                    placeholder="Firebase API key">
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Blynk Configuration -->
                                    <div id="blynk-config" style="display: none;">
                                        <div class="mb-3">
                                            <label class="form-label">Blynk Auth Token</label>
                                            <div class="input-group">
                                                <span class="input-group-text">
                                                    <i class="fas fa-key"></i>
                                                </span>
                                                <input type="text" class="form-control" id="blynk_token" 
                                                    placeholder="Your Blynk auth token">
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Blynk Server</label>
                                            <div class="input-group">
                                                <span class="input-group-text">
                                                    <i class="fas fa-server"></i>
                                                </span>
                                                <input type="text" class="form-control" id="blynk_server" 
                                                    value="blynk.cloud" placeholder="Blynk server">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card mb-3">
                                <div class="card-header">
                                    <h6 class="mb-0">Device Information</h6>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label class="form-label">Device Key</label>
                                        <div class="input-group">
                                            <input type="text" class="form-control" value="{{ $device->device_key }}" readonly>
                                            <button class="btn btn-outline-secondary" type="button" onclick="copyDeviceKey()">
                                                <i class="fas fa-copy"></i>
                                            </button>
                                        </div>
                                        <small class="text-muted">This is your unique device identifier</small>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Status</label>
                                        <div>
                                            <span class="badge {{ $device->is_online ? 'bg-success' : 'bg-danger' }}">
                                                {{ $device->is_online ? 'Online' : 'Offline' }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card mt-4">
                        <div class="card-header">
                            <h6 class="mb-0">GPIO Pin Configuration</h6>
                        </div>
                        <div class="card-body">
                            <div class="row" id="pin-grid">
                                @foreach($availablePins as $pin)
                                <div class="col-md-3 mb-3">
                                    <div class="card">
                                        <div class="card-body">
                                            <h6 class="card-title">GPIO {{ $pin['number'] }}</h6>
                                            <div class="mb-2">
                                                <label class="form-label">Function</label>
                                                <select class="form-select" id="pin-{{ $pin['number'] }}-function" 
                                                    onchange="updatePinConfig({{ $pin['number'] }})">
                                                    <option value="">Not Used</option>
                                                    @foreach($pin['capabilities'] as $capability)
                                                        <option value="{{ $capability }}">
                                                            {{ ucwords(str_replace('_', ' ', $capability)) }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div id="pin-{{ $pin['number'] }}-config" style="display: none;">
                                                <div class="mb-2">
                                                    <label class="form-label">Name</label>
                                                    <input type="text" class="form-control" 
                                                        id="pin-{{ $pin['number'] }}-name" 
                                                        placeholder="Pin name">
                                                </div>
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" 
                                                        id="pin-{{ $pin['number'] }}-active">
                                                    <label class="form-check-label">Active</label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
.card {
    border-radius: 10px;
}
.card-header {
    border-top-left-radius: 10px;
    border-top-right-radius: 10px;
}
.form-check-input:checked {
    background-color: #0d6efd;
    border-color: #0d6efd;
}
</style>
@endpush

@push('scripts')
<script>
function togglePassword() {
    const input = document.getElementById('wifi_password');
    const icon = document.getElementById('password-toggle-icon');
    
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}

function copyDeviceKey() {
    const input = document.querySelector('input[value="{{ $device->device_key }}"]');
    input.select();
    document.execCommand('copy');
    alert('Device key copied to clipboard!');
}

function updatePinConfig(pinNumber) {
    const select = document.getElementById(`pin-${pinNumber}-function`);
    const config = document.getElementById(`pin-${pinNumber}-config`);
    
    if (select.value) {
        config.style.display = 'block';
    } else {
        config.style.display = 'none';
    }
}

function updateProtocolConfig() {
    const protocol = document.getElementById('protocol').value;
    const configs = ['mqtt-config', 'http-config', 'websocket-config', 'firebase-config', 'blynk-config'];
    
    configs.forEach(config => {
        document.getElementById(config).style.display = 'none';
    });
    
    document.getElementById(`${protocol}-config`).style.display = 'block';
}

function saveConfiguration() {
    const protocol = document.getElementById('protocol').value;
    const config = {
        wifi_ssid: document.getElementById('wifi_ssid').value,
        wifi_password: document.getElementById('wifi_password').value,
        protocol: protocol,
        pins: []
    };

    // Add protocol-specific configuration
    switch(protocol) {
        case 'mqtt':
            config.mqtt = {
                server: document.getElementById('mqtt_server').value,
                username: document.getElementById('mqtt_username').value,
                password: document.getElementById('mqtt_password').value
            };
            break;
        case 'http':
            config.http = {
                endpoint: document.getElementById('http_endpoint').value,
                api_key: document.getElementById('http_api_key').value
            };
            break;
        case 'websocket':
            config.websocket = {
                server: document.getElementById('ws_server').value,
                token: document.getElementById('ws_token').value
            };
            break;
        case 'firebase':
            config.firebase = {
                project_id: document.getElementById('firebase_project_id').value,
                api_key: document.getElementById('firebase_api_key').value
            };
            break;
        case 'blynk':
            config.blynk = {
                token: document.getElementById('blynk_token').value,
                server: document.getElementById('blynk_server').value
            };
            break;
    }

    // Collect pin configurations
    @foreach($availablePins as $pin)
    const pin{{ $pin['number'] }} = document.getElementById(`pin-{{ $pin['number'] }}-function`);
    if (pin{{ $pin['number'] }}.value) {
        config.pins.push({
            number: {{ $pin['number'] }},
            function: pin{{ $pin['number'] }}.value,
            name: document.getElementById(`pin-{{ $pin['number'] }}-name`).value,
            is_active: document.getElementById(`pin-{{ $pin['number'] }}-active`).checked
        });
    }
    @endforeach

    // Send configuration to server
    fetch(`/api/devices/{{ $device->id }}/configure`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify(config)
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            alert('Configuration saved successfully!');
            window.location.href = '{{ route('devices.show', $device) }}';
        } else {
            alert('Failed to save configuration: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to save configuration');
    });
}

// Initialize existing pin configurations
document.addEventListener('DOMContentLoaded', function() {
    @foreach($device->pins as $pin)
    const pin{{ $pin->pin_number }} = document.getElementById(`pin-{{ $pin->pin_number }}-function`);
    if (pin{{ $pin->pin_number }}) {
        pin{{ $pin->pin_number }}.value = '{{ $pin->type }}';
        document.getElementById(`pin-{{ $pin->pin_number }}-name`).value = '{{ $pin->name }}';
        document.getElementById(`pin-{{ $pin->pin_number }}-active`).checked = {{ $pin->is_active ? 'true' : 'false' }};
        updatePinConfig({{ $pin->pin_number }});
    }
    @endforeach
    updateProtocolConfig();
});
</script>
@endpush
@endsection 