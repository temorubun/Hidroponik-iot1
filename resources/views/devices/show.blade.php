@extends('layouts.dashboard')

@push('meta')
<meta name="device-id" content="{{ $device->id }}">
@endpush

@push('styles')
<link href="{{ asset('css/device-details.css') }}" rel="stylesheet">
<link href="{{ asset('css/projects.css') }}" rel="stylesheet">
@endpush

@section('content')
    @component('layouts.content-layout')
        @slot('breadcrumb')
            <a href="{{ route('devices.index') }}" class="dashboard-link" data-aos="fade-down" data-aos-delay="50">
                <i class="fas fa-microchip me-2"></i>
                Devices
            </a>
        @endslot

        @slot('title', $device->name)
        @slot('subtitle', $device->description ?? 'No description provided')
        @slot('icon', 'fas fa-microchip')
        
        @slot('actions')
            <a href="{{ route('devices.edit', $device) }}" class="btn btn-primary" data-aos="fade-left" data-aos-delay="100">
                <i class="fas fa-edit me-2"></i>Edit Device
            </a>
            <a href="{{ route('devices.code', $device) }}" class="btn btn-primary" data-aos="fade-left" data-aos-delay="200">
                <i class="fas fa-code me-2"></i>View IoT Code
            </a>
        @endslot

        <div class="row">
            <div class="col-12">
                <!-- Device Info Card -->
                <div class="card shadow-sm mb-4" data-aos="fade-up" data-aos-delay="100">
                    <div class="card-body">
                        <div class="row">
                            <!-- Left Column -->
                            <div class="col-md-6 border-end" data-aos="fade-right" data-aos-delay="200">
                                <div class="p-3">
                                    <!-- WiFi Configuration Section -->
                                    <div class="mb-0">
                                        <h6 class="text-uppercase text-muted mb-2">WiFi Configuration</h6>
                                        <div class="config-info">
                                            <div class="mb-3">
                                                <div class="d-flex align-items-center mb-2">
                                                    <i class="fas fa-wifi text-primary me-2"></i>
                                                    <span class="fw-medium">SSID:</span>
                                                </div>
                                                <div class="ps-4">{{ $device->wifi_ssid ?: 'Not configured' }}</div>
                                            </div>
                                            <div>
                                                <div class="d-flex align-items-center mb-2">
                                                    <i class="fas fa-key text-primary me-2"></i>
                                                    <span class="fw-medium">Password:</span>
                                                </div>
                                                <div class="ps-4 d-flex align-items-center">
                                                    <span id="wifi-password" class="password-field">
                                                        {{ $device->wifi_password ?: 'Not configured' }}
                                                    </span>
                                                    <button class="btn btn-link text-primary p-0 ms-2" onclick="togglePasswordVisibility()">
                                                        <i class="fas fa-eye" id="password-toggle-icon"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Right Column -->
                            <div class="col-md-6" data-aos="fade-left" data-aos-delay="200">
                                <div class="p-3">
                                    <!-- Status Section -->
                                    <div class="mb-4">
                                        <h6 class="text-uppercase text-muted mb-2">Status</h6>
                                        <div class="d-flex align-items-center gap-3">
                                            <span class="badge bg-{{ $device->is_online ? 'success' : 'danger' }} rounded-pill device-status-badge" data-device-id="{{ $device->id }}">
                                                <i class="fas fa-circle me-1"></i>
                                                {{ $device->is_online ? 'Online' : 'Offline' }}
                                            </span>
                                            <button onclick="rebootESP()" class="btn btn-warning btn-sm rounded-pill" id="rebootBtn" data-device-id="{{ $device->id }}">
                                                <i class="fas fa-sync me-2"></i>Reboot ESP
                                            </button>
                                        </div>
                                    </div>

                                    <!-- IP Address Section -->
                                    <div class="mb-4">
                                        <h6 class="text-uppercase text-muted mb-2">IP Address</h6>
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-network-wired text-primary me-2"></i>
                                            <span id="device-ip-text" data-device-id="{{ $device->id }}">{{ $device->is_online ? 'Loading...' : 'Not Connected' }}</span>
                                        </div>
                                    </div>

                                    <!-- Device Key Section -->
                                    <div class="mb-0">
                                        <h6 class="text-uppercase text-muted mb-2">Device Key</h6>
                                        <div class="input-group">
                                            <input type="text" class="form-control" value="{{ $device->device_key }}" readonly>
                                            <button class="btn btn-outline-primary" type="button" onclick="copyDeviceKey()">
                                                <i class="fas fa-copy"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Device Pins Section -->
                <div class="card shadow-sm" data-aos="fade-up" data-aos-delay="300">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div class="d-flex align-items-center gap-2">
                                <i class="fas fa-plug text-primary"></i>
                                <h5 class="mb-0">Device Pins</h5>
                            </div>
                            <a href="{{ route('pins.create', $device) }}" class="btn btn-primary d-flex align-items-center gap-2">
                                <i class="fas fa-plus"></i>
                                <span>Add New Pin</span>
                            </a>
                        </div>

                        @if($device->pins->isEmpty())
                            <div class="text-center py-5" data-aos="fade-up" data-aos-delay="400">
                                <div class="mb-4">
                                    <i class="fas fa-plug fa-4x text-muted"></i>
                                </div>
                                <h5 class="text-muted mb-3">No Pins Configured</h5>
                                <p class="text-muted mb-4">Start by adding a new pin to your device</p>
                                <a href="{{ route('pins.create', $device) }}" class="btn btn-primary">
                                    <i class="fas fa-plus me-2"></i>Add New Pin
                                </a>
                            </div>
                        @else
                            <div class="row g-4">
                                @foreach($device->pins as $pin)
                                    <div class="col-md-6" data-aos="fade-up" data-aos-delay="{{ 400 + $loop->index * 100 }}">
                                        <div class="pin-card d-flex flex-column">
                                            <div class="flex-grow-1">
                                                <div class="pin-header">
                                                    <h5 class="pin-name">{{ $pin->name }}</h5>
                                                    <span class="pin-status {{ $pin->is_active ? 'active' : 'inactive' }}">
                                                        {{ $pin->is_active ? 'Active' : 'Inactive' }}
                                                    </span>
                                                </div>

                                                <div class="pin-type">
                                                    <i class="fas fa-microchip"></i>
                                                    <span>{{ ucfirst(str_replace('_', ' ', $pin->type)) }}</span>
                                                </div>

                                                <div class="pin-number">
                                                    <i class="fas fa-hashtag"></i>
                                                    <span>GPIO {{ $pin->pin_number }}</span>
                                                </div>

                                                @if($pin->type === 'digital_output')
                                                    <div class="form-check form-switch mb-4" data-pin-number="{{ $pin->pin_number }}">
                                                        <input class="form-check-input" type="checkbox" 
                                                            id="pin-{{ $pin->id }}" 
                                                            {{ $pin->value ? 'checked' : '' }}
                                                            data-device-id="{{ $device->id }}"
                                                            data-pin-number="{{ $pin->pin_number }}"
                                                            onchange="updatePinValue({{ $pin->id }}, this.checked)">
                                                        <label class="form-check-label" for="pin-{{ $pin->id }}">
                                                            {{ $pin->value ? 'ON' : 'OFF' }}
                                                        </label>
                                                    </div>
                                                @else
                                                    <div class="pin-value">
                                                        <span class="value-label">Current Value:</span>
                                                        @if($pin->type === 'ph_sensor')
                                                            <span id="value-{{ $pin->id }}" class="value-text ph-value" data-device-id="{{ $device->id }}">
                                                                {{ number_format($pin->value, 1) }} pH
                                                            </span>
                                                        @else
                                                            <span id="value-{{ $pin->id }}" class="value-text" data-device-id="{{ $device->id }}">
                                                                {{ $pin->value }}
                                                            </span>
                                                        @endif
                                                    </div>
                                                @endif
                                            </div>

                                            <a href="{{ route('pins.show', ['device' => $device->uuid, 'pin' => $pin->uuid]) }}" class="edit-btn mt-4 text-decoration-none">
                                                <i class="fas fa-eye"></i>
                                                <span>Show Pin</span>
                                            </a>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endcomponent
@endsection

@push('scripts')
<script>
    const deviceKey = "{{ $device->device_key }}";
    const wsUrl = "{{ $device->getWebSocketUrl() }}";
</script>
<script src="{{ asset('js/device-details.js') }}"></script>
@endpush