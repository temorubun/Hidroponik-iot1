@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-between mb-4">
        <div class="col-auto">
            <h2><i class="fas fa-microchip"></i> My IoT Devices</h2>
        </div>
        <div class="col-auto">
            <a href="{{ route('devices.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> New Device
            </a>
        </div>
    </div>

    @if($devices->isEmpty())
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="fas fa-microchip fa-3x text-muted mb-3"></i>
                <h5>No Devices Yet</h5>
                <p class="text-muted">Start by creating your first IoT device</p>
                <a href="{{ route('devices.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Create Device
                </a>
            </div>
        </div>
    @else
        <div class="row">
            @foreach($devices as $device)
                <div class="col-md-4 mb-4">
                    <div class="card h-100 shadow-sm">
                        <div class="card-header bg-white">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">{{ $device->name }}</h5>
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

                            <div class="d-grid gap-2">
                                <a href="{{ route('devices.show', $device) }}" class="btn btn-outline-primary">
                                    <i class="fas fa-eye"></i> View Device
                                </a>
                            </div>
                        </div>
                        <div class="card-footer bg-white">
                            <small class="text-muted">
                                <i class="fas fa-clock"></i> Created {{ $device->created_at->diffForHumans() }}
                            </small>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>

@push('styles')
<style>
.card {
    transition: transform 0.2s;
    border-radius: 10px;
}
.card:hover {
    transform: translateY(-5px);
}
.card-header {
    border-top-left-radius: 10px;
    border-top-right-radius: 10px;
}
.badge {
    font-weight: 500;
    padding: 0.5em 0.75em;
}
</style>
@endpush
@endsection 