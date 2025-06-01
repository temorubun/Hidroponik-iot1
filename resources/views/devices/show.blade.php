@extends('layouts.app')

@push('meta')
<meta name="device-id" content="{{ $device->id }}">
@endpush

@section('content')
<div class="container">
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fas fa-microchip"></i> Device Details</h5>
                        <div>
                            <a href="{{ route('devices.code', $device) }}" class="btn btn-light btn-sm">
                                <i class="fas fa-code"></i> View IoT Code
                            </a>
                            <a href="{{ route('devices.edit', $device) }}" class="btn btn-light btn-sm">
                                <i class="fas fa-edit"></i> Edit Device
                            </a>
                            <a href="{{ route('devices.index') }}" class="btn btn-light btn-sm ms-2">
                                <i class="fas fa-arrow-left"></i> Back
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-4">
                                <h6 class="text-muted mb-2">Name</h6>
                                <p class="h5">{{ $device->name }}</p>
                            </div>
                            
                            <div class="mb-4">
                                <h6 class="text-muted mb-2">Description</h6>
                                <p>{{ $device->description ?: 'No description' }}</p>
                            </div>

                            <div class="mb-4">
                                <h6 class="text-muted mb-2">WiFi Configuration</h6>
                                <p class="mb-1">
                                    <i class="fas fa-wifi me-2"></i>
                                    SSID: {{ $device->wifi_ssid ?: 'Not configured' }}
                                </p>
                                <p class="mb-1">
                                    <i class="fas fa-key me-2"></i>
                                    Password: 
                                    <span class="position-relative">
                                        <span id="wifi-password" style="filter: blur(4px);">
                                            {{ $device->wifi_password ?: 'Not configured' }}
                                        </span>
                                        <button class="btn btn-sm btn-link text-primary p-0 ms-2" onclick="togglePasswordVisibility()">
                                            <i class="fas fa-eye" id="password-toggle-icon"></i>
                                        </button>
                                    </span>
                                </p>
                                @if($device->wifi_qr_code)
                                    <div class="mt-2">
                                        <img src="{{ asset('storage/' . $device->wifi_qr_code) }}" alt="WiFi QR Code" class="img-thumbnail" style="max-width: 150px;">
                                    </div>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-4">
                                <h6 class="text-muted mb-2">Status</h6>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="badge {{ $device->is_online ? 'bg-success' : 'bg-danger' }} p-2 device-status-badge">
                                        <i class="fas {{ $device->is_online ? 'fa-check-circle' : 'fa-times-circle' }} me-1"></i>
                                        {{ $device->is_online ? 'Online' : 'Offline' }}
                                    </span>
                                    <button onclick="rebootESP()" class="btn btn-warning btn-sm" id="rebootBtn">
                                        <i class="fas fa-sync"></i> Reboot ESP
                                    </button>
                                </div>
                            </div>

                            <div class="mb-4">
                                <h6 class="text-muted mb-2">IP Address</h6>
                                <p class="device-ip mb-0">
                                    <i class="fas fa-network-wired me-1"></i>
                                    <span id="device-ip-text">{{ $device->is_online ? 'Loading...' : 'Not Connected' }}</span>
                                </p>
                            </div>
                            
                            <div class="mb-4">
                                <h6 class="text-muted mb-2">Device Key</h6>
                                <div class="input-group">
                                    <input type="text" class="form-control" value="{{ $device->device_key }}" readonly>
                                    <button class="btn btn-outline-secondary" type="button" onclick="copyDeviceKey()">
                                        <i class="fas fa-copy"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fas fa-plug"></i> Device Pins</h5>
                        <button onclick="addNewPin()" class="btn btn-light btn-sm">
                            <i class="fas fa-plus"></i> Add New Pin
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    @if($device->pins->isEmpty())
                        <div class="text-center py-5">
                            <i class="fas fa-plug fa-3x text-muted mb-3"></i>
                            <h5>No Pins Configured</h5>
                            <p class="text-muted">Start by adding a new pin to your device</p>
                            <a href="{{ route('pins.create', $device) }}" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Add New Pin
                            </a>
                        </div>
                    @else
                        <div class="row">
                            @foreach($device->pins as $pin)
                                <div class="col-md-4 mb-4">
                                    <div class="card h-100 shadow-sm">
                                        <div class="card-header bg-white">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <h6 class="mb-0">{{ $pin->name }}</h6>
                                                <span class="badge {{ $pin->is_active ? 'bg-success' : 'bg-secondary' }}">
                                                    {{ $pin->is_active ? 'Active' : 'Inactive' }}
                                                </span>
                                            </div>
                                        </div>
                                        <div class="card-body">
                                            <div class="mb-3">
                                                <small class="text-muted">
                                                    <i class="fas fa-{{ $pin->settings['icon'] ?? 'microchip' }}"></i>
                                                    {{ ucfirst(str_replace('_', ' ', $pin->type)) }}
                                                </small>
                                                <br>
                                                <small class="text-muted">
                                                    <i class="fas fa-hashtag"></i>
                                                    GPIO {{ $pin->pin_number }}
                                                </small>
                                            </div>

                                            @if($pin->type === 'digital_output')
                                                <div class="form-check form-switch mb-3" data-pin-number="{{ $pin->pin_number }}">
                                                    <input class="form-check-input" type="checkbox" 
                                                        id="pin-{{ $pin->id }}" 
                                                        {{ $pin->value ? 'checked' : '' }}
                                                        onchange="updatePinValue({{ $pin->id }}, this.checked)">
                                                    <label class="form-check-label" for="pin-{{ $pin->id }}">
                                                        {{ $pin->value ? 'ON' : 'OFF' }}
                                                    </label>
                                                </div>
                                            @else
                                                <p class="mb-2">
                                                    Current Value: 
                                                    @if($pin->type === 'ph_sensor')
                                                        <span id="value-{{ $pin->id }}" class="ph-value">
                                                            {{ number_format($pin->value, 1) }} pH
                                                        </span>
                                                    @else
                                                        <span id="value-{{ $pin->id }}">{{ $pin->value }}</span>
                                                    @endif
                                                </p>
                                            @endif

                                            <div class="d-grid">
                                                <a href="{{ route('pins.edit', [$device, $pin]) }}" class="btn btn-outline-primary btn-sm">
                                                    <i class="fas fa-edit"></i> Edit Pin
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
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
.badge {
    font-weight: 500;
    padding: 0.5em 0.75em;
}
.form-check-input {
    cursor: pointer;
}
.card.shadow-sm {
    transition: transform 0.2s;
}
.card.shadow-sm:hover {
    transform: translateY(-5px);
}
</style>
@endpush

@push('scripts')
<script>
    let ws;
    const deviceKey = "{{ $device->device_key }}";
    const wsUrl = "{{ $device->getWebSocketUrl() }}";

    function initWebSocket() {
        console.log('[WebSocket] Configuration:', {
            url: wsUrl,
            deviceKey: deviceKey
        });
        
        if (ws) {
            console.log('[WebSocket] Closing existing connection...');
            ws.close();
        }

        ws = new WebSocket(wsUrl);

        ws.onopen = function() {
            console.log('[WebSocket] Connected to server');
            const authMessage = {
                type: 'web_auth',
                device_key: deviceKey
            };
            console.log('[WebSocket] Sending auth message:', authMessage);
            ws.send(JSON.stringify(authMessage));
        };

        ws.onmessage = function(event) {
            try {
                const data = JSON.parse(event.data);
                console.log('[WebSocket] Received message:', {
                    type: data.type,
                    data: data
                });

                if (data.type === 'device_status') {
                    console.log('[WebSocket] Updating device status:', data);
                    updateDeviceStatus(data.is_online, data.ip_address);
                }
                else if (data.type === 'pin_update') {
                    console.log('[WebSocket] Updating pin status:', data);
                    updatePinStatus(data.pin_number, data.value);
                }
                else if (data.type === 'sensor_update') {
                    console.log('[WebSocket] Updating sensor data:', data);
                    const pinElement = document.getElementById('value-' + data.pin.id);
                    if (pinElement) {
                        if (pinElement.classList.contains('ph-value')) {
                            // Format pH value
                            pinElement.textContent = formatPhValue(data.pin.value);
                            // Update timestamp if element exists
                            const timestampElement = document.getElementById('timestamp-' + data.pin.id);
                            if (timestampElement) {
                                timestampElement.textContent = 'Last update: ' + new Date(data.timestamp).toLocaleString();
                            }
                        } else {
                            pinElement.textContent = data.pin.value;
                        }
                    } else {
                        console.warn('[WebSocket] Element not found for pin:', data.pin.id);
                    }
                }
                else if (data.type === 'pin_configured') {
                    console.log('[WebSocket] Pin configuration confirmed:', data);
                    if (data.status === 'success') {
                        console.log('[WebSocket] Pin configured successfully');
                    } else {
                        alert('Failed to configure pin on device');
                    }
                }
                else if (data.status === 'error') {
                    console.error('[WebSocket] Error from server:', data.message);
                    alert(data.message);
                    // Re-enable any disabled switches
                    document.querySelectorAll('.form-check-input:disabled').forEach(input => {
                        input.disabled = false;
                        // Revert the switch state
                        input.checked = !input.checked;
                    });
                }
            } catch (error) {
                console.error('[WebSocket] Error parsing message:', error, 'Raw:', event.data);
            }
        };

        ws.onclose = function(event) {
            console.log('[WebSocket] Connection closed:', {
                code: event.code,
                reason: event.reason,
                wasClean: event.wasClean
            });
            setTimeout(initWebSocket, 5000);
        };

        ws.onerror = function(error) {
            console.error('[WebSocket] Error:', error);
        };
    }

    // Fungsi untuk memformat nilai pH
    function formatPhValue(value) {
        return parseFloat(value).toFixed(1) + ' pH';
    }

    function updateDeviceStatus(isOnline, ipAddress) {
        const statusBadge = document.querySelector('.device-status-badge');
        const ipText = document.querySelector('#device-ip-text');
        
        if (statusBadge) {
            statusBadge.className = `badge ${isOnline ? 'bg-success' : 'bg-danger'} p-2 device-status-badge`;
            statusBadge.innerHTML = `
                <i class="fas ${isOnline ? 'fa-check-circle' : 'fa-times-circle'} me-1"></i>
                ${isOnline ? 'Online' : 'Offline'}
            `;
        }

        if (ipText) {
            ipText.textContent = isOnline ? ipAddress : 'Not Connected';
        }
    }

    function updatePinStatus(pinNumber, value) {
        const pinElement = document.querySelector(`[data-pin-number="${pinNumber}"]`);
        if (pinElement) {
            // Update status pin di UI
            const statusElement = pinElement.querySelector('.pin-status');
            if (statusElement) {
                statusElement.textContent = value;
            }

            // Update toggle button jika ada
            const toggleButton = pinElement.querySelector('.pin-toggle');
            if (toggleButton) {
                toggleButton.checked = value === '1' || value === 1;
            }
        }
    }

    function updatePinValue(pinId, value) {
        const pin = document.getElementById(`pin-${pinId}`).closest('[data-pin-number]');
        if (!pin) {
            console.error('[ERROR] Could not find pin element');
            return;
        }
        
        const pinNumber = parseInt(pin.dataset.pinNumber);
        if (isNaN(pinNumber)) {
            console.error('[ERROR] Invalid pin number');
            return;
        }
        
        console.log('[PIN] Updating pin:', {
            pinId: pinId,
            pinNumber: pinNumber,
            value: value,
            deviceKey: deviceKey
        });
        
        // Disable the switch while processing
        const input = document.getElementById(`pin-${pinId}`);
        input.disabled = true;

        // Send pin control message through WebSocket
        if (!ws || ws.readyState !== WebSocket.OPEN) {
            console.error('[ERROR] WebSocket not connected');
            alert('Error: WebSocket not connected. Please refresh the page.');
            input.disabled = false;
            input.checked = !value;
            return;
        }

        const message = {
            type: 'pin',
            device_key: deviceKey,
            pin: pinNumber,
            value: value ? 1 : 0
        };

        try {
            ws.send(JSON.stringify(message));
            console.log('[WebSocket] Sent pin control message:', message);
            
            // Update UI optimistically
            const label = document.querySelector(`label[for="pin-${pinId}"]`);
            if (label) {
                label.textContent = value ? 'ON' : 'OFF';
            }
            
            // Enable the switch after a short delay
            setTimeout(() => {
                input.disabled = false;
            }, 500);
        } catch (error) {
            console.error('[WebSocket] Error sending message:', error);
            alert('Failed to send command to device');
            input.disabled = false;
            input.checked = !value;
        }
    }

    function copyDeviceKey() {
        const input = document.querySelector('input[value="{{ $device->device_key }}"]');
        input.select();
        document.execCommand('copy');
        alert('Device key copied to clipboard!');
    }

    function togglePasswordVisibility() {
        const password = document.getElementById('wifi-password');
        const icon = document.getElementById('password-toggle-icon');
        
        if (password.style.filter === 'blur(4px)') {
            password.style.filter = 'none';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            password.style.filter = 'blur(4px)';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    }

    function addNewPin() {
        window.location.href = "{{ route('pins.create', $device) }}";
    }

    function rebootESP() {
        if (!ws || ws.readyState !== WebSocket.OPEN) {
            alert('WebSocket connection not ready. Please try again.');
            return;
        }

        const rebootBtn = document.getElementById('rebootBtn');
        rebootBtn.disabled = true;
        rebootBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Rebooting...';

        const rebootMessage = {
            type: 'reboot',
            device_key: deviceKey
        };

        try {
            ws.send(JSON.stringify(rebootMessage));
            console.log('[WebSocket] Sent reboot command');
            
            // Re-enable button after 5 seconds
            setTimeout(() => {
                rebootBtn.disabled = false;
                rebootBtn.innerHTML = '<i class="fas fa-sync"></i> Reboot ESP';
            }, 5000);
        } catch (error) {
            console.error('[WebSocket] Error sending reboot command:', error);
            alert('Failed to send reboot command');
            rebootBtn.disabled = false;
            rebootBtn.innerHTML = '<i class="fas fa-sync"></i> Reboot ESP';
        }
    }

    // Initialize WebSocket when page loads
    document.addEventListener('DOMContentLoaded', function() {
        initWebSocket();
        
        // Format existing pH values on page load
        document.querySelectorAll('.ph-value').forEach(function(element) {
            const value = parseFloat(element.textContent);
            if (!isNaN(value)) {
                element.textContent = formatPhValue(value);
            }
        });
    });
</script>
@endpush
@endsection