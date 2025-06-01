@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-between mb-4">
        <div class="col-auto">
            <h2><i class="fas fa-chart-line"></i> Pin Charts</h2>
        </div>
        <div class="col-auto">
            <a href="{{ route('pins.index') }}" class="btn btn-primary">
                <i class="fas fa-th"></i> View All Pins
            </a>
        </div>
    </div>

    @if($pins->isEmpty())
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="fas fa-chart-line fa-3x text-muted mb-3"></i>
                <h5>No Data to Display</h5>
                <p class="text-muted">Configure pins to start collecting data</p>
            </div>
        </div>
    @else
        <div class="row">
            @foreach($pins as $pin)
                <div class="col-md-6 mb-4">
                    <div class="card shadow-sm">
                        <div class="card-header bg-white">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-0">{{ $pin->name }}</h6>
                                    <small class="text-muted">{{ $pin->device->name }}</small>
                                </div>
                                <div class="btn-group btn-group-sm">
                                    <button type="button" class="btn btn-outline-secondary active" 
                                        onclick="updateChart({{ $pin->id }}, 'day')">Day</button>
                                    <button type="button" class="btn btn-outline-secondary" 
                                        onclick="updateChart({{ $pin->id }}, 'week')">Week</button>
                                    <button type="button" class="btn btn-outline-secondary" 
                                        onclick="updateChart({{ $pin->id }}, 'month')">Month</button>
                                </div>
                            </div>
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

                            <div class="chart-container" style="position: relative; height: 200px;">
                                <canvas id="chart-{{ $pin->id }}"></canvas>
                            </div>

                            <div class="row mt-3 text-center">
                                <div class="col">
                                    <small class="text-muted d-block">Average</small>
                                    <strong id="avg-{{ $pin->id }}">-</strong>
                                </div>
                                <div class="col">
                                    <small class="text-muted d-block">Minimum</small>
                                    <strong id="min-{{ $pin->id }}">-</strong>
                                </div>
                                <div class="col">
                                    <small class="text-muted d-block">Maximum</small>
                                    <strong id="max-{{ $pin->id }}">-</strong>
                                </div>
                            </div>
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
    border-radius: 10px;
}
.card-header {
    border-top-left-radius: 10px;
    border-top-right-radius: 10px;
}
.btn-group {
    border-radius: 5px;
    overflow: hidden;
}
.btn-group .btn {
    border-radius: 0;
}
.btn-group .btn:first-child {
    border-top-left-radius: 5px;
    border-bottom-left-radius: 5px;
}
.btn-group .btn:last-child {
    border-top-right-radius: 5px;
    border-bottom-right-radius: 5px;
}
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const charts = {};

function initChart(pinId) {
    const ctx = document.getElementById(`chart-${pinId}`).getContext('2d');
    charts[pinId] = new Chart(ctx, {
        type: 'line',
        data: {
            labels: [],
            datasets: [{
                label: 'Value',
                data: [],
                borderColor: '#3b82f6',
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                borderWidth: 2,
                fill: true,
                tension: 0.4
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
                    beginAtZero: true
                },
                x: {
                    grid: {
                        display: false
                    }
                }
            }
        }
    });
    return charts[pinId];
}

function updateChart(pinId, range = 'day') {
    // Update active button state
    const buttons = document.querySelector(`#chart-${pinId}`).closest('.card').querySelectorAll('.btn-group .btn');
    buttons.forEach(btn => {
        btn.classList.remove('active');
        if (btn.textContent.toLowerCase() === range) {
            btn.classList.add('active');
        }
    });

    fetch(`/api/pins/${pinId}/chart-data?range=${range}`)
        .then(response => response.json())
        .then(data => {
            const chart = charts[pinId] || initChart(pinId);
            chart.data.labels = data.timestamps;
            chart.data.datasets[0].data = data.values;
            chart.update();

            // Update statistics
            document.getElementById(`avg-${pinId}`).textContent = 
                typeof data.stats.avg === 'number' ? data.stats.avg.toFixed(2) : '-';
            document.getElementById(`min-${pinId}`).textContent = 
                typeof data.stats.min === 'number' ? data.stats.min.toFixed(2) : '-';
            document.getElementById(`max-${pinId}`).textContent = 
                typeof data.stats.max === 'number' ? data.stats.max.toFixed(2) : '-';
        })
        .catch(error => {
            console.error('Error:', error);
        });
}

// Initialize all charts with day range
document.addEventListener('DOMContentLoaded', function() {
    @foreach($pins as $pin)
        updateChart({{ $pin->id }}, 'day');
    @endforeach

    // Update charts every 30 seconds
    setInterval(function() {
        @foreach($pins as $pin)
            updateChart({{ $pin->id }}, 'day');
        @endforeach
    }, 30000);
});
</script>
@endpush
@endsection 