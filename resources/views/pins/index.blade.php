@extends('layouts.dashboard')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4" data-aos="fade-up">
        <div class="d-flex align-items-center">
            <div class="iot-icon-wrapper me-3">
            <i class="fas fa-plug text-teal"></i>
            </div>
            <h1 class="h2 mb-0 text-iot">IoT Pins Overview</h1>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('pins.charts') }}" class="btn btn-primary" data-aos="fade-left" data-aos-delay="100">
                <i class="fas fa-chart-line me-2"></i> View Charts
            </a>
        </div>
    </div>

    @if($pins->isEmpty())
        <div class="card shadow-sm" data-aos="fade-up" data-aos-delay="200">
            <div class="card-body text-center py-5">
                <div class="empty-state-icon mb-4">
                    <i class="fas fa-microchip fa-2x"></i>
                </div>
                <h4 class="mb-3 fw-bold text-iot">No Pins Configured</h4>
                <p class="text-muted mb-4 px-4 mx-auto" style="max-width: 400px;">Start by adding pins to your devices to monitor and control their states</p>
                <a href="{{ route('pins.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i> Add New Pin
                </a>
            </div>
        </div>
    @else
        <div class="row g-4">
            @foreach($pins as $pin)
            <div class="col-md-6 col-lg-4" data-aos="fade-up" data-aos-delay="{{ $loop->iteration * 100 }}">
                <div class="card h-100 pin-card">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <h5 class="card-title mb-1">{{ $pin->name }}</h5>
                                <div class="d-flex align-items-center gap-2 text-muted small">
                                    <i class="fas fa-microchip"></i>
                                    <span>{{ $pin->device->name }}</span>
                                </div>
                            </div>
                            <span class="badge bg-{{ $pin->type === 'analog' ? 'info' : 'warning' }}">
                                {{ ucfirst($pin->type) }}
                            </span>
                        </div>
                        
                        <div class="d-flex align-items-center mb-4">
                            <div class="me-4">
                                <small class="text-muted d-block mb-1">Pin Number</small>
                                <span class="fw-medium">
                                    <i class="fas fa-hashtag me-1 text-primary"></i>
                                    {{ $pin->pin_number }}
                                </span>
                            </div>
                            <div>
                                <small class="text-muted d-block mb-1">Last Value</small>
                                <span class="fw-medium">
                                    <i class="fas fa-signal me-1 text-primary"></i>
                                    {{ $pin->last_value ?? 'N/A' }}
                                </span>
                            </div>
                        </div>

                        <div class="d-flex gap-2">
                            <!-- Debug info -->
                            <div class="d-none">
                                Device UUID: {{ $pin->device->uuid }}
                                Pin UUID: {{ $pin->uuid }}
                            </div>
                            <a href="{{ route('pins.show', ['device' => $pin->device->uuid, 'pin' => $pin->uuid]) }}" class="btn btn-primary flex-grow-1">
                                <i class="fas fa-eye me-2 text-white"></i> Show PIN
                            </a>
                            <a href="{{ route('pins.edit', ['device' => $pin->device->uuid, 'pin' => $pin->uuid]) }}" class="btn btn-light" title="Edit Pin">
                                <i class="fas fa-edit"></i>
                            </a>
                            @if($pin->type === 'digital')
                            <button type="button" class="btn btn-light toggle-pin" data-pin-id="{{ $pin->uuid }}" data-device-id="{{ $pin->device->uuid }}" title="Toggle Pin">
                                <i class="fas fa-power-off"></i>
                            </button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    @endif
</div>
@endsection

@push('styles')
<link href="{{ asset('css/pins.css') }}" rel="stylesheet">
@endpush

@push('scripts')
<script>
document.querySelectorAll('.toggle-pin').forEach(button => {
    button.addEventListener('click', function() {
        const pinId = this.dataset.pinId;
        const deviceId = this.dataset.deviceId;
        
        // Add loading state
        button.disabled = true;
        button.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
        
        fetch(`/devices/${deviceId}/pins/${pinId}/status`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        })
        .then(response => response.json())
        .then(data => {
            button.disabled = false;
            button.innerHTML = '<i class="fas fa-power-off"></i>';
            
            if (data.success) {
                button.classList.add('btn-success');
                setTimeout(() => {
                    button.classList.remove('btn-success');
                }, 1000);
            } else {
                button.classList.add('btn-danger');
                setTimeout(() => {
                    button.classList.remove('btn-danger');
                }, 1000);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            button.disabled = false;
            button.innerHTML = '<i class="fas fa-power-off"></i>';
            button.classList.add('btn-danger');
            setTimeout(() => {
                button.classList.remove('btn-danger');
            }, 1000);
        });
    });
});
</script>
@endpush 