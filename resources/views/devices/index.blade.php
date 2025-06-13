@extends('layouts.dashboard')

@section('content')
    @component('layouts.content-layout')
        @slot('title', 'My IoT Devices')
        @slot('icon', 'fas fa-microchip')
        
        @slot('actions')
            <a href="{{ route('devices.create') }}" class="btn btn-primary" data-aos="fade-left" data-aos-delay="100">
                <i class="fas fa-plus me-2"></i>New Device
            </a>
        @endslot

        @if($devices->isEmpty())
            <div class="col-12">
                <div class="project-card" data-aos="fade-up" data-aos-delay="200">
                    <div class="card-body text-center py-5">
                        <div class="empty-state-icon mb-4">
                            <i class="fas fa-microchip fa-4x gradient-text"></i>
                        </div>
                        <h4 class="mb-3 fw-bold">No Devices Yet</h4>
                        <p class="text-muted mb-4 px-4 mx-auto" style="max-width: 400px;">
                            Start by creating your first IoT device to begin monitoring and controlling your pins
                        </p>
                        <a href="{{ route('devices.create') }}" class="btn btn-primary btn-lg">
                            <i class="fas fa-plus me-2"></i>Create Device
                        </a>
                    </div>
                </div>
            </div>
        @else
            <div class="row g-4">
                @foreach($devices as $device)
                <div class="col-md-6 col-lg-4" data-aos="fade-up" data-aos-delay="{{ $loop->iteration * 100 }}">
                    <div class="project-card h-100">
                        <div class="card-body p-4">
                            <!-- Device Header -->
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div>
                                    <h5 class="card-title mb-1 fw-bold gradient-text">{{ $device->name }}</h5>
                                    <div class="d-flex align-items-center gap-2 text-muted small">
                                        <i class="fas fa-project-diagram"></i>
                                        <span>{{ $device->project->name }}</span>
                                    </div>
                                </div>
                                <span class="status-badge {{ $device->is_online ? 'status-online' : 'status-offline' }}" data-device-id="{{ $device->id }}">
                                    <i class="fas fa-circle me-1"></i>
                                    {{ $device->is_online ? 'Online' : 'Offline' }}
                                </span>
                            </div>
                            
                            <!-- Device Stats -->
                            <div class="d-flex align-items-center mb-4">
                                <div class="me-4">
                                    <small class="text-muted d-block mb-1">Last Seen</small>
                                    <div class="d-flex align-items-center text-muted" data-timestamp="{{ $device->last_seen ? $device->last_seen->format('Y-m-d H:i:s') : 'Never' }}" data-device-id="{{ $device->id }}">
                                        <i class="fas fa-clock me-2"></i>
                                        {{ $device->last_seen ? $device->last_seen->diffForHumans() : 'Never' }}
                                    </div>
                                </div>
                                <div>
                                    <small class="text-muted d-block mb-1">Pins</small>
                                    <div class="d-flex align-items-center text-muted">
                                        <i class="fas fa-plug me-2"></i>
                                        {{ $device->pins->count() }} Configured
                                    </div>
                                </div>
                            </div>

                            <!-- Actions -->
                            <div class="d-flex gap-2">
                                <a href="{{ route('devices.show', $device) }}" class="btn btn-primary flex-grow-1">
                                    View Device
                                </a>
                                @if($device->pins->isNotEmpty())
                                <a href="{{ route('pins.charts') }}?device={{ $device->id }}" class="btn btn-light">
                                    <i class="fas fa-chart-line"></i>
                                </a>
                                @endif
                                <a href="{{ route('devices.edit', $device) }}" class="btn btn-light">
                                    <i class="fas fa-edit"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        @endif
    @endcomponent
@endsection

@push('styles')
<link href="{{ asset('css/projects.css') }}" rel="stylesheet">
<style>
.status-badge {
    padding: 0.5em 1em;
    border-radius: 2rem;
    font-weight: 500;
    font-size: 0.75rem;
    color: white;
}

.status-online {
    background: linear-gradient(135deg, #2ECC71 0%, #27AE60 100%);
}

.status-offline {
    background: linear-gradient(135deg, #E74C3C 0%, #C0392B 100%);
}

.empty-state-icon {
    width: 80px;
    height: 80px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto;
    background: rgba(255, 255, 255, 0.9);
    border-radius: 50%;
    box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
}
</style>
@endpush 