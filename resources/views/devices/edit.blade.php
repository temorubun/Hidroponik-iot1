@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fas fa-edit"></i> Edit Device: {{ $device->name }}</h5>
                        <a href="{{ route('devices.show', $device) }}" class="btn btn-light btn-sm">
                            <i class="fas fa-arrow-left"></i> Back
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    <form method="POST" action="{{ route('devices.update', $device) }}" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label for="name" class="form-label">Device Name</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas fa-microchip"></i>
                                </span>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                    id="name" name="name" value="{{ old('name', $device->name) }}" required>
                            </div>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas fa-align-left"></i>
                                </span>
                                <textarea class="form-control @error('description') is-invalid @enderror" 
                                    id="description" name="description" rows="3">{{ old('description', $device->description) }}</textarea>
                            </div>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="wifi_ssid" class="form-label">WiFi SSID</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas fa-wifi"></i>
                                </span>
                                <input type="text" class="form-control @error('wifi_ssid') is-invalid @enderror" 
                                    id="wifi_ssid" name="wifi_ssid" value="{{ old('wifi_ssid', $device->wifi_ssid) }}">
                            </div>
                            @error('wifi_ssid')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="wifi_password" class="form-label">WiFi Password</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas fa-key"></i>
                                </span>
                                <input type="password" class="form-control @error('wifi_password') is-invalid @enderror" 
                                    id="wifi_password" name="wifi_password" value="{{ old('wifi_password', $device->wifi_password) }}">
                                <button class="btn btn-outline-secondary" type="button" onclick="togglePassword()">
                                    <i class="fas fa-eye" id="password-toggle-icon"></i>
                                </button>
                            </div>
                            @error('wifi_password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="wifi_qr_code" class="form-label">WiFi QR Code</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas fa-qrcode"></i>
                                </span>
                                <input type="file" class="form-control @error('wifi_qr_code') is-invalid @enderror" 
                                    id="wifi_qr_code" name="wifi_qr_code">
                            </div>
                            @if($device->wifi_qr_code)
                                <div class="mt-2">
                                    <img src="{{ asset('storage/' . $device->wifi_qr_code) }}" alt="WiFi QR Code" class="img-thumbnail" style="max-width: 200px;">
                                </div>
                            @endif
                            @error('wifi_qr_code')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> You can manage device pins from the device details page.
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Update Device
                            </button>

                            <button type="button" class="btn btn-danger" onclick="confirmDelete()">
                                <i class="fas fa-trash"></i> Delete Device
                            </button>
                        </div>
                    </form>

                    <form id="delete-form" action="{{ route('devices.destroy', $device) }}" method="POST" class="d-none">
                        @csrf
                        @method('DELETE')
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
.form-control {
    border-left: none;
}
.form-control:focus {
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
function confirmDelete() {
    if (confirm('Are you sure you want to delete this device? This action cannot be undone.')) {
        document.getElementById('delete-form').submit();
    }
}

function togglePassword() {
    const passwordInput = document.getElementById('wifi_password');
    const icon = document.getElementById('password-toggle-icon');
    
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        passwordInput.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}
</script>
@endpush
@endsection 