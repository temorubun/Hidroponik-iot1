@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-between mb-4">
        <div class="col-auto">
            <h2><i class="fas fa-plug"></i> All IoT Pins</h2>
        </div>
        <div class="col-auto">
            <a href="{{ route('pins.charts') }}" class="btn btn-primary">
                <i class="fas fa-chart-line"></i> View Charts
            </a>
        </div>
    </div>

    @if($pins->isEmpty())
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="fas fa-plug fa-3x text-muted mb-3"></i>
                <h5>No Pins Configured</h5>
                <p class="text-muted">Add pins to your devices to get started</p>
            </div>
        </div>
    @else
        <div class="row">
            @foreach($pins as $pin)
                <div class="col-md-4 mb-4">
                    <div class="card h-100 shadow-sm">
                        <div class="card-header bg-white">
                            <div class="d-flex justify-content-between align-items-center">
                                <h6 class="mb-0">{{ $pin->name }}</h6>
                                <span class="badge {{ $pin->is_active ? 'bg-success' : 'bg-secondary' }}">
                                    {{ $pin->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </div>
                            <small class="text-muted">{{ $pin->device->name }}</small>
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
                                <div class="form-check form-switch mb-3">
                                    <input class="form-check-input" type="checkbox" 
                                        id="pin-{{ $pin->id }}" 
                                        {{ $pin->value ? 'checked' : '' }}
                                        onchange="updatePinValue({{ $pin->device->id }}, {{ $pin->id }}, this.checked)">
                                    <label class="form-check-label" for="pin-{{ $pin->id }}">
                                        {{ $pin->value ? 'ON' : 'OFF' }}
                                    </label>
                                </div>
                            @else
                                <p class="mb-2">
                                    Current Value: <span id="value-{{ $pin->id }}">{{ $pin->value }}</span>
                                </p>
                            @endif

                            <div class="d-grid">
                                <a href="{{ route('pins.edit', [$pin->device, $pin]) }}" class="btn btn-outline-primary btn-sm">
                                    <i class="fas fa-edit"></i> Edit Pin
                                </a>
                            </div>
                        </div>
                        <div class="card-footer bg-white">
                            <small class="text-muted">
                                <i class="fas fa-clock"></i> Updated {{ $pin->updated_at->diffForHumans() }}
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
.form-check-input {
    cursor: pointer;
}
</style>
@endpush

@push('scripts')
<script>
function updatePinValue(deviceId, pinId, value) {
    fetch(`/api/devices/${deviceId}/pins/${pinId}/status`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            value: value ? 1 : 0,
            is_active: true
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            const label = document.querySelector(`label[for="pin-${pinId}"]`);
            label.textContent = value ? 'ON' : 'OFF';
        } else {
            alert('Failed to update pin value');
            // Revert the switch state
            const input = document.getElementById(`pin-${pinId}`);
            input.checked = !value;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to update pin value');
        // Revert the switch state
        const input = document.getElementById(`pin-${pinId}`);
        input.checked = !value;
    });
}
</script>
@endpush
@endsection 