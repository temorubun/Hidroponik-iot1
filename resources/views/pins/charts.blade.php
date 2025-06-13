@extends('layouts.dashboard')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4" data-aos="fade-up">
        <div>
            <h1 class="h2 mb-0 gradient-text">Pin Charts</h1>
            <p class="text-muted mb-0">Monitor all your pins in real-time</p>
        </div>
    </div>

    <div class="row g-4">
        @forelse($pins as $pin)
        <div class="col-lg-6" data-aos="fade-up" data-aos-delay="{{ $loop->iteration * 100 }}">
            <div class="card h-100">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <h5 class="gradient-text mb-1">{{ $pin->name }}</h5>
                            <p class="text-muted mb-0">Pin {{ $pin->pin_number }} - {{ ucfirst($pin->type) }}</p>
                        </div>
                        <a href="{{ route('pins.chart', ['device' => $pin->device_id, 'pin' => $pin->id]) }}" 
                           class="btn btn-light btn-sm">
                            <i class="fas fa-expand-alt me-2"></i>Full View
                        </a>
                    </div>

                    <div class="chart-container" style="position: relative; height: 200px;">
                        <canvas id="chart-{{ $pin->id }}"></canvas>
                    </div>

                    <div class="d-flex justify-content-between mt-3">
                        <div class="text-center">
                            <small class="text-muted d-block">Current</small>
                            <strong class="gradient-text" id="current-{{ $pin->id }}">{{ $pin->last_value ?: 'N/A' }}</strong>
                        </div>
                        <div class="text-center">
                            <small class="text-muted d-block">Average</small>
                            <strong class="gradient-text" id="avg-{{ $pin->id }}">-</strong>
                        </div>
                        <div class="text-center">
                            <small class="text-muted d-block">Min</small>
                            <strong class="gradient-text" id="min-{{ $pin->id }}">-</strong>
                        </div>
                        <div class="text-center">
                            <small class="text-muted d-block">Max</small>
                            <strong class="gradient-text" id="max-{{ $pin->id }}">-</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @empty
        <div class="col-12">
            <div class="card" data-aos="fade-up">
                <div class="card-body p-4 text-center">
                    <i class="fas fa-chart-line fa-3x text-muted mb-3"></i>
                    <h5>No Pins Found</h5>
                    <p class="text-muted mb-0">Create some pins to start monitoring their data.</p>
                </div>
            </div>
        </div>
        @endforelse
    </div>
</div>

@push('styles')
<style>
:root {
    --gradient-start: #00BFA6;
    --gradient-end: #0093E9;
    --gradient-mix: linear-gradient(135deg, var(--gradient-start) 0%, var(--gradient-end) 100%);
    --shadow-color: rgba(0, 191, 166, 0.15);
}

.container-fluid {
    padding: 2rem;
    margin-top: 4rem; /* Add margin to account for fixed navbar */
    position: relative;
    z-index: 1;
}

@media (min-width: 992px) {
    .container-fluid {
        margin-left: var(--sidebar-width);
        width: calc(100% - var(--sidebar-width));
        padding-top: 2rem;
    }
}

@media (max-width: 991.98px) {
    .container-fluid {
        margin-left: 0;
        width: 100%;
        padding: 1rem;
        margin-top: 4rem; /* Increase top margin on mobile */
    }
}

.gradient-text {
    background: var(--gradient-mix);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    position: relative;
}

.card {
    border: none;
    box-shadow: 0 4px 6px var(--shadow-color);
    transition: all 0.3s ease;
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(10px);
}

.card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 15px var(--shadow-color);
}

.btn-light {
    border: none;
    background: white;
    color: var(--text-secondary);
    transition: all 0.3s ease;
    box-shadow: 0 2px 4px var(--shadow-color);
}

.btn-light:hover {
    background: var(--gradient-mix);
    color: white;
}

.chart-container {
    margin: 1rem 0;
}

/* Fix for content being hidden under navbar */
.content-wrapper {
    padding-top: 1rem;
}

/* Ensure cards don't get hidden */
.row.g-4 {
    margin-top: 0;
}
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const charts = {};

document.addEventListener('DOMContentLoaded', function() {
    @foreach($pins as $pin)
    initializeChart({{ $pin->id }});
    loadData({{ $pin->id }});
    @endforeach
});

function initializeChart(pinId) {
    const ctx = document.getElementById(`chart-${pinId}`).getContext('2d');
    charts[pinId] = new Chart(ctx, {
        type: 'line',
        data: {
            labels: [],
            datasets: [{
                label: 'Value',
                data: [],
                borderColor: '#00BFA6',
                backgroundColor: 'rgba(0, 191, 166, 0.1)',
                borderWidth: 2,
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(0, 0, 0, 0.05)'
                    }
                },
                x: {
                    grid: {
                        display: false
                    }
                }
            }
        }
    });
}

function loadData(pinId) {
    fetch(`/api/pins/${pinId}/chart-data?range=day`)
        .then(response => response.json())
        .then(data => {
            updateChart(pinId, data);
            updateStats(pinId, data);
        })
        .catch(error => console.error('Error loading data:', error));
}

function updateChart(pinId, data) {
    charts[pinId].data.labels = data.timestamps;
    charts[pinId].data.datasets[0].data = data.values;
    charts[pinId].update();
}

function updateStats(pinId, data) {
    document.getElementById(`avg-${pinId}`).textContent = data.stats.avg.toFixed(2);
    document.getElementById(`min-${pinId}`).textContent = data.stats.min.toFixed(2);
    document.getElementById(`max-${pinId}`).textContent = data.stats.max.toFixed(2);
}

// WebSocket connection for real-time updates
const ws = new WebSocket(`ws://${window.location.hostname}:6001`);
ws.onmessage = function(event) {
    const data = JSON.parse(event.data);
    const element = document.getElementById(`current-${data.pin_id}`);
    if (element) {
        element.textContent = data.value;
    }
};
</script>
@endpush 