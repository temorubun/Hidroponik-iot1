@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-project-diagram"></i> {{ $project->name }}
                        </h5>
                        <div>
                            <a href="{{ route('projects.edit', $project) }}" class="btn btn-light btn-sm">
                                <i class="fas fa-edit"></i> Edit Project
                            </a>
                            <a href="{{ route('projects.index') }}" class="btn btn-light btn-sm ms-2">
                                <i class="fas fa-arrow-left"></i> Back
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="mb-4">
                                <h6 class="text-muted mb-2">Description</h6>
                                <p>{{ $project->description ?: 'No description provided' }}</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-4">
                                <h6 class="text-muted mb-2">Status</h6>
                                <span class="badge bg-{{ $project->status === 'active' ? 'success' : ($project->status === 'inactive' ? 'warning' : 'secondary') }}">
                                    {{ ucfirst($project->status) }}
                                </span>
                            </div>
                            <div class="mb-4">
                                <h6 class="text-muted mb-2">Created</h6>
                                <p class="mb-0">{{ $project->created_at->format('d M Y, H:i') }}</p>
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
                        <h5 class="mb-0">
                            <i class="fas fa-microchip"></i> Project Devices
                        </h5>
                        <a href="{{ route('devices.create') }}" class="btn btn-light btn-sm">
                            <i class="fas fa-plus"></i> Add Device
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if($project->devices->isEmpty())
                        <div class="text-center py-5">
                            <i class="fas fa-microchip fa-3x text-muted mb-3"></i>
                            <h5>No Devices Yet</h5>
                            <p class="text-muted">Start by adding your first IoT device</p>
                            <a href="{{ route('devices.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Add Device
                            </a>
                        </div>
                    @else
                        <div class="row">
                            @foreach($project->devices as $device)
                                <div class="col-md-4 mb-4">
                                    <div class="card h-100 shadow-sm">
                                        <div class="card-header bg-white">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <h6 class="mb-0">{{ $device->name }}</h6>
                                                <span class="badge {{ $device->is_online ? 'bg-success' : 'bg-danger' }}">
                                                    {{ $device->is_online ? 'Online' : 'Offline' }}
                                                </span>
                                            </div>
                                        </div>
                                        <div class="card-body">
                                            <p class="text-muted">{{ $device->description ?: 'No description' }}</p>
                                            
                                            <div class="mb-3">
                                                <small class="text-muted">
                                                    <i class="fas fa-clock"></i> 
                                                    Last seen: {{ $device->last_online ? $device->last_online->diffForHumans() : 'Never' }}
                                                </small>
                                                <br>
                                                <small class="text-muted">
                                                    <i class="fas fa-plug"></i> 
                                                    Pins: {{ $device->pins->count() }}
                                                </small>
                                            </div>

                                            <div class="d-grid">
                                                <a href="{{ route('devices.show', $device) }}" class="btn btn-outline-primary btn-sm">
                                                    <i class="fas fa-eye"></i> View Device
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
    border-radius: 10px;
    border: none;
}
.card-header {
    border-top-left-radius: 10px !important;
    border-top-right-radius: 10px !important;
    border-bottom: 1px solid rgba(0,0,0,.125);
}
.badge {
    font-weight: 500;
    padding: 0.5em 0.75em;
}
.card.shadow-sm {
    transition: transform 0.2s;
}
.card.shadow-sm:hover {
    transform: translateY(-5px);
}
</style>
@endpush
@endsection 