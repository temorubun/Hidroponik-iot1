@extends('layouts.dashboard')

@section('content')
    @component('layouts.content-layout')
        @slot('breadcrumb')
            <a href="{{ route('devices.index') }}" class="dashboard-link">
                <i class="fas fa-microchip me-2"></i>
                Devices
            </a>
        @endslot

        @slot('title', 'Edit Device')
        @slot('subtitle', 'Update your device settings below')
        @slot('icon', 'fas fa-microchip')

        @include('components.confirm-modal', [
            'title' => 'Delete Device',
            'message' => 'Are you sure you want to delete this device? This action cannot be undone.',
            'formId' => 'deleteDeviceForm'
        ])

        <div class="col-lg-8 mx-auto">
            <div class="project-card" data-aos="fade-up" data-aos-duration="1000">
                <div class="card-body p-4">
                    <form method="POST" action="{{ route('devices.update', $device) }}" class="form-centered">
                        @csrf
                        @method('PUT')

                        <!-- Device Name -->
                        <div class="form-group mb-4" data-aos="fade-up" data-aos-delay="200">
                            <label for="name" class="form-label">
                                <i class="fas fa-microchip me-2 gradient-icon"></i><span class="gradient-text">Device Name</span>
                            </label>
                            <div class="input-group input-group-custom">
                                <span class="input-group-text">
                                    <i class="fas fa-tag gradient-icon"></i>
                                </span>
                                <input type="text" class="form-control form-control-custom @error('name') is-invalid @enderror"
                                       id="name" name="name" value="{{ old('name', $device->name) }}"
                                       placeholder="Enter device name" required>
                                @error('name')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                            <small class="form-text text-muted mt-2">Choose a unique and descriptive name for your device</small>
                        </div>

                        <!-- Description -->
                        <div class="form-group mb-4" data-aos="fade-up" data-aos-delay="300">
                            <label for="description" class="form-label">
                                <i class="fas fa-align-left me-2 gradient-icon"></i><span class="gradient-text">Description</span>
                            </label>
                            <div class="input-group input-group-custom">
                                <span class="input-group-text">
                                    <i class="fas fa-comment gradient-icon"></i>
                                </span>
                                <textarea class="form-control form-control-custom @error('description') is-invalid @enderror"
                                          id="description" name="description" rows="4"
                                          placeholder="Enter device description">{{ old('description', $device->description) }}</textarea>
                                @error('description')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                            <small class="form-text text-muted mt-2">Provide a brief description of your device (optional)</small>
                        </div>

                        <!-- WiFi SSID -->
                        <div class="form-group mb-4" data-aos="fade-up" data-aos-delay="600">
                            <label for="wifi_ssid" class="form-label">
                                <i class="fas fa-wifi me-2 gradient-icon"></i><span class="gradient-text">WiFi SSID</span>
                            </label>
                            <div class="input-group input-group-custom">
                                <span class="input-group-text">
                                    <i class="fas fa-broadcast-tower gradient-icon"></i>
                                </span>
                                <input type="text" class="form-control form-control-custom @error('wifi_ssid') is-invalid @enderror"
                                       id="wifi_ssid" name="wifi_ssid" value="{{ old('wifi_ssid', $device->wifi_ssid) }}"
                                       placeholder="Enter WiFi network name">
                                @error('wifi_ssid')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                            <small class="form-text text-muted mt-2">Enter the WiFi network name for your device to connect to</small>
                        </div>

                        <!-- WiFi Password -->
                        <div class="form-group mb-4" data-aos="fade-up" data-aos-delay="700">
                            <label for="wifi_password" class="form-label">
                                <i class="fas fa-key me-2 gradient-icon"></i><span class="gradient-text">WiFi Password</span>
                            </label>
                            <div class="input-group input-group-custom">
                                <span class="input-group-text">
                                    <i class="fas fa-lock gradient-icon"></i>
                                </span>
                                <input type="password" class="form-control form-control-custom @error('wifi_password') is-invalid @enderror"
                                       id="wifi_password" name="wifi_password" value="{{ old('wifi_password', $device->wifi_password) }}"
                                       placeholder="Enter WiFi password">
                                <button class="btn btn-outline-secondary" type="button" onclick="togglePassword()">
                                    <i class="fas fa-eye" id="password-toggle-icon"></i>
                                </button>
                                @error('wifi_password')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                            <small class="form-text text-muted mt-2">Enter the WiFi password (will be encrypted)</small>
                        </div>

                        <div class="form-actions d-flex justify-content-between align-items-center mt-5" data-aos="fade-up" data-aos-delay="800">
                            <a href="{{ route('devices.show', $device) }}" class="btn btn-light">
                                <i class="fas fa-arrow-left me-2"></i>Back
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Save Changes
                            </button>
                        </div>
                    </form>

                    <div class="danger-zone" data-aos="fade-up" data-aos-delay="900">
                        <h5 class="danger-zone-title">
                            <i class="fas fa-exclamation-triangle"></i>
                            Danger Zone
                        </h5>
                        <form method="POST" action="{{ route('devices.destroy', $device) }}" id="deleteDeviceForm">
                            @csrf
                            @method('DELETE')
                            <button type="button" class="btn btn-outline-danger" onclick="confirmDelete('deleteDeviceForm')">
                                <i class="fas fa-trash-alt me-2"></i>Delete Device
                            </button>
                        </form>
                    </div>
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
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

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
</script>
@endpush 