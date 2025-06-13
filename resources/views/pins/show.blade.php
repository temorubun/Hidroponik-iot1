@extends('layouts.dashboard')

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4" data-aos="fade-up">
        <div class="d-flex align-items-center">
            <div class="project-icon me-3">
                <i class="fas fa-plug"></i>
            </div>
            <div>
                <h1 class="h2 mb-0 gradient-text">Pin Details</h1>
                <p class="text-muted mb-0">{{ $pin->name }}</p>
            </div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('pins.index') }}" class="btn btn-light">
                <i class="fas fa-arrow-left me-2"></i>Back to Pins
            </a>
            <a href="{{ route('pins.edit', ['device' => $pin->device->uuid, 'pin' => $pin->uuid]) }}" class="btn btn-primary">
                <i class="fas fa-edit me-2"></i>Edit Pin
            </a>
        </div>
    </div>

    <div class="row g-4">
        <!-- Basic Information -->
        <div class="col-lg-4" data-aos="fade-up">
            <div class="card dashboard-card h-100">
                <div class="card-body p-4">
                    <h5 class="gradient-text mb-4">Basic Information</h5>
                    
                    <div class="mb-4">
                        <label class="text-muted small mb-1">Device</label>
                        <div class="d-flex align-items-center">
                            <i class="fas fa-microchip me-2 text-primary"></i>
                            <h6 class="mb-0">{{ $pin->device->name }}</h6>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="text-muted small mb-1">Pin Number</label>
                        <div class="d-flex align-items-center">
                            <i class="fas fa-hashtag me-2 text-primary"></i>
                            <h6 class="mb-0">{{ $pin->pin_number }}</h6>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="text-muted small mb-1">Type</label>
                        <div class="d-flex align-items-center">
                            <i class="fas fa-plug me-2 text-primary"></i>
                            <h6 class="mb-0">{{ ucfirst(str_replace('_', ' ', $pin->type)) }}</h6>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="text-muted small mb-1">Status</label>
                        <div>
                            @if($pin->is_active)
                                <span class="badge bg-success">Active</span>
                            @else
                                <span class="badge bg-danger">Inactive</span>
                            @endif
                        </div>
                    </div>

                    @if($pin->type === 'digital_output')
                    <div class="mt-4">
                        <button type="button" class="btn btn-primary w-100 toggle-pin" data-pin-id="{{ $pin->id }}" data-device-id="{{ $pin->device_id }}">
                            <i class="fas fa-power-off me-2"></i>Toggle Pin
                        </button>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Current Value & Chart -->
        <div class="col-lg-8" data-aos="fade-up" data-aos-delay="100">
            <div class="card dashboard-card h-100">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="gradient-text mb-0">Real-time Monitoring</h5>
                        <div class="btn-group">
                            <button type="button" class="btn btn-light active" data-range="hour">1h</button>
                            <button type="button" class="btn btn-light" data-range="day">24h</button>
                            <button type="button" class="btn btn-light" data-range="week">7d</button>
                        </div>
                    </div>

                    <!-- Current Value -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="stat-card">
                                <small class="text-muted d-block mb-1">Current Value</small>
                                <h4 class="mb-0 gradient-text" id="currentValue">
                                    @if($pin->type === 'digital_output')
                                        {{ $pin->last_value ? 'ON' : 'OFF' }}
                                    @elseif($pin->type === 'ph_sensor')
                                        {{ number_format($pin->last_value, 1) ?? 'N/A' }} pH
                                    @else
                                        {{ $pin->last_value ?? 'N/A' }}
                                    @endif
                                </h4>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stat-card">
                                <small class="text-muted d-block mb-1">Average</small>
                                <h4 class="mb-0 gradient-text" id="avgValue">-</h4>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stat-card">
                                <small class="text-muted d-block mb-1">Minimum</small>
                                <h4 class="mb-0 gradient-text" id="minValue">-</h4>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stat-card">
                                <small class="text-muted d-block mb-1">Maximum</small>
                                <h4 class="mb-0 gradient-text" id="maxValue">-</h4>
                            </div>
                        </div>
                    </div>

                    <!-- Chart -->
                    <div class="chart-container" style="position: relative; height: 300px;">
                        <canvas id="pinChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Additional Settings -->
        <div class="col-12" data-aos="fade-up" data-aos-delay="200">
            <div class="card dashboard-card">
                <div class="card-body p-4">
                    <h5 class="gradient-text mb-4">Additional Settings</h5>
                    
                    <div class="row">
                        <!-- Alert Settings -->
                        <div class="col-md-6 mb-4">
                            <h6 class="mb-3">Alert Settings</h6>
                            <div class="stat-card">
                                <div class="mb-3">
                                    <label class="text-muted small mb-1">Status</label>
                                    <div>
                                        @if(isset($pin->settings['alerts']['enabled']) && $pin->settings['alerts']['enabled'])
                                            <span class="badge bg-success">Enabled</span>
                                        @else
                                            <span class="badge bg-secondary">Disabled</span>
                                        @endif
                                    </div>
                                </div>
                                @if(isset($pin->settings['alerts']['enabled']) && $pin->settings['alerts']['enabled'])
                                    @if(isset($pin->settings['alerts']['min_threshold']))
                                    <div class="mb-2">
                                        <label class="text-muted small mb-1">Min Threshold</label>
                                        <div>{{ $pin->settings['alerts']['min_threshold'] }}</div>
                                    </div>
                                    @endif
                                    @if(isset($pin->settings['alerts']['max_threshold']))
                                    <div>
                                        <label class="text-muted small mb-1">Max Threshold</label>
                                        <div>{{ $pin->settings['alerts']['max_threshold'] }}</div>
                                    </div>
                                    @endif
                                @endif
                            </div>
                        </div>

                        <!-- Schedule Settings -->
                        <div class="col-md-6 mb-4">
                            <h6 class="mb-3">Schedule Settings</h6>
                            <div class="stat-card">
                                <div class="mb-3">
                                    <label class="text-muted small mb-1">Status</label>
                                    <div>
                                        @if(isset($pin->settings['schedule']['enabled']) && $pin->settings['schedule']['enabled'])
                                            <span class="badge bg-success">Enabled</span>
                                        @else
                                            <span class="badge bg-secondary">Disabled</span>
                                        @endif
                                    </div>
                                </div>
                                @if(isset($pin->settings['schedule']['enabled']) && $pin->settings['schedule']['enabled'])
                                    @if(isset($pin->settings['schedule']['interval']))
                                    <div class="mb-2">
                                        <label class="text-muted small mb-1">Interval</label>
                                        <div>{{ $pin->settings['schedule']['interval'] }} minutes</div>
                                    </div>
                                    @endif
                                    @if(isset($pin->settings['schedule']['duration']))
                                    <div>
                                        <label class="text-muted small mb-1">Duration</label>
                                        <div>{{ $pin->settings['schedule']['duration'] }} minutes</div>
                                    </div>
                                    @endif
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link href="{{ asset('css/pin-details.css') }}" rel="stylesheet">
<style>
    .stat-card {
        background: white;
        border-radius: 8px;
        padding: 1rem;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        transition: all 0.3s ease;
    }
    
    .stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    }
    
    .gradient-text {
        background: linear-gradient(45deg, #2193b0, #6dd5ed);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }
    
    .chart-container {
        background: white;
        border-radius: 8px;
        padding: 1rem;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        margin-bottom: 2rem;
    }
</style>
@endpush

@push('scripts')
<!-- Include Chart.js from CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
    // Pass PHP variables to JavaScript
    const pinId = {{ $pin->id }};
    const pinType = '{{ $pin->type }}';
    const currentValue = @json($pin->last_value);
</script>
<script src="{{ asset('js/pin-details.js') }}"></script>
@endpush 