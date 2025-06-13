@extends('layouts.dashboard')

@section('content')
    @component('layouts.content-layout')
        @slot('breadcrumb')
            <a href="{{ route('devices.index') }}" class="dashboard-link">
                <i class="fas fa-microchip me-2"></i>
                Devices
            </a>
        @endslot

        @slot('title', 'Create New Device')
        @slot('subtitle', 'Fill in the details below to create your new IoT device')
        @slot('icon', 'fas fa-microchip')

        <div class="col-lg-8 mx-auto">
            <div class="project-card" data-aos="fade-up" data-aos-duration="1000">
                <div class="card-body p-4">
                    <form method="POST" action="{{ route('devices.store') }}" class="form-centered">
                        @csrf

                        <!-- Project Selection -->
                        <div class="form-group mb-4" data-aos="fade-up" data-aos-delay="200">
                            <label for="project_id" class="form-label">
                                <i class="fas fa-project-diagram me-2 gradient-icon"></i><span class="gradient-text">Project</span>
                            </label>
                            <div class="input-group input-group-custom">
                                <span class="input-group-text">
                                    <i class="fas fa-folder gradient-icon"></i>
                                </span>
                                <select class="form-select form-control-custom @error('project_id') is-invalid @enderror"
                                        id="project_id" name="project_id" required>
                                    <option value="">Select Project</option>
                                    @foreach($projects as $project)
                                        <option value="{{ $project->id }}" 
                                            {{ old('project_id', request('project')) == $project->id ? 'selected' : '' }}>
                                            {{ $project->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('project_id')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                            <small class="form-text text-muted mt-2">Choose the project this device belongs to</small>
                        </div>

                        <!-- Device Name -->
                        <div class="form-group mb-4" data-aos="fade-up" data-aos-delay="300">
                            <label for="name" class="form-label">
                                <i class="fas fa-microchip me-2 gradient-icon"></i><span class="gradient-text">Device Name</span>
                            </label>
                            <div class="input-group input-group-custom">
                                <span class="input-group-text">
                                    <i class="fas fa-tag gradient-icon"></i>
                                </span>
                                <input type="text" class="form-control form-control-custom @error('name') is-invalid @enderror"
                                       id="name" name="name" value="{{ old('name') }}"
                                       placeholder="Enter device name" required>
                                @error('name')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                            <small class="form-text text-muted mt-2">Choose a unique and descriptive name for your device</small>
                        </div>

                        <!-- Device Token -->
                        <div class="form-group mb-4" data-aos="fade-up" data-aos-delay="400">
                            <label for="token" class="form-label">
                                <i class="fas fa-key me-2 gradient-icon"></i><span class="gradient-text">Device Token</span>
                                <i class="fas fa-question-circle ms-2" 
                                   data-bs-toggle="tooltip" 
                                   title="This token will be used to authenticate your device"></i>
                            </label>
                            <div class="input-group input-group-custom">
                                <span class="input-group-text">
                                    <i class="fas fa-lock gradient-icon"></i>
                                </span>
                                <input type="text" class="form-control form-control-custom @error('token') is-invalid @enderror"
                                       id="token" name="token" value="{{ old('token', Str::random(32)) }}"
                                       placeholder="Device token" required readonly>
                                <button type="button" class="btn btn-light" onclick="generateToken()">
                                    <i class="fas fa-sync-alt"></i>
                                </button>
                                @error('token')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                            <small class="form-text text-muted mt-2">This token will be used to authenticate your device with the API</small>
                        </div>

                        <!-- Description -->
                        <div class="form-group mb-4" data-aos="fade-up" data-aos-delay="500">
                            <label for="description" class="form-label">
                                <i class="fas fa-align-left me-2 gradient-icon"></i><span class="gradient-text">Description</span>
                            </label>
                            <div class="input-group input-group-custom">
                                <span class="input-group-text">
                                    <i class="fas fa-comment gradient-icon"></i>
                                </span>
                                <textarea class="form-control form-control-custom @error('description') is-invalid @enderror"
                                          id="description" name="description" rows="4"
                                          placeholder="Enter device description">{{ old('description') }}</textarea>
                                @error('description')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                            <small class="form-text text-muted mt-2">Provide a brief description of your device (optional)</small>
                        </div>

                        <div class="form-actions d-flex justify-content-between align-items-center mt-5" data-aos="fade-up" data-aos-delay="600">
                            <a href="{{ route('devices.index') }}" class="btn btn-light">
                                <i class="fas fa-arrow-left me-2"></i>Back
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Create Device
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
    function generateToken() {
        const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
        let token = '';
        for (let i = 0; i < 32; i++) {
            token += chars.charAt(Math.floor(Math.random() * chars.length));
        }
        document.getElementById('token').value = token;
    }

    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
</script>
@endpush 