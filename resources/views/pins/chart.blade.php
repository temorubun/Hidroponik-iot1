@extends('layouts.dashboard')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4" data-aos="fade-up">
        <div>
            <h1 class="h2 mb-0 gradient-text">{{ $pin->name }} Chart</h1>
            <p class="text-muted mb-0">Pin {{ $pin->pin_number }} - {{ ucfirst($pin->type) }}</p>
        </div>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-primary" onclick="refreshData()">
                <i class="fas fa-sync-alt me-2"></i>Refresh Data
            </button>
        </div>
    </div>

    <div class="row g-4">
        <!-- Current Value Card -->
        <div class="col-lg-4">
            <div class="card h-100" data-aos="fade-up" data-aos-delay="100">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="gradient-text mb-0">Current Value</h5>
                        <span class="badge bg-success">Live</span>
                    </div>
                    
                    <div class="text-center">
                        <div class="current-value mb-3">
                            <span id="currentValue">{{ $pin->last_value ?: 'N/A' }}</span>
                            <small class="text-muted d-block">Last updated: <span id="lastUpdate">{{ $pin->last_read_at ? $pin->last_read_at->diffForHumans() : 'Never' }}</span></small>
                        </div>

                        <div class="pin-status mb-4">
                            @if($pin->is_active)
                                <span class="badge bg-success">Active</span>
                            @else
                                <span class="badge bg-danger">Inactive</span>
                            @endif
                        </div>

                        <div class="chart-filters">
                            <div class="btn-group" role="group">
                                <button type="button" class="btn btn-light active" data-range="day">24h</button>
                                <button type="button" class="btn btn-light" data-range="week">7d</button>
                                <button type="button" class="btn btn-light" data-range="month">30d</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Chart Card -->
        <div class="col-lg-8">
            <div class="card h-100" data-aos="fade-up" data-aos-delay="200">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="gradient-text mb-0">Statistics</h5>
                        <div class="chart-type-selector">
                            <div class="btn-group">
                                <button type="button" class="btn btn-light active" data-chart-type="line">
                                    <i class="fas fa-chart-line"></i>
                                </button>
                                <button type="button" class="btn btn-light" data-chart-type="bar">
                                    <i class="fas fa-chart-bar"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="chart-container" style="position: relative; height: 300px;">
                        <canvas id="pinChart"></canvas>
                    </div>

                    <div class="row mt-4">
                        <div class="col-md-4">
                            <div class="stat-item text-center">
                                <h6 class="text-muted mb-1">Average</h6>
                                <h4 class="gradient-text mb-0" id="avgValue">-</h4>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="stat-item text-center">
                                <h6 class="text-muted mb-1">Minimum</h6>
                                <h4 class="gradient-text mb-0" id="minValue">-</h4>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="stat-item text-center">
                                <h6 class="text-muted mb-1">Maximum</h6>
                                <h4 class="gradient-text mb-0" id="maxValue">-</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
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
    position: relative;
    z-index: 1;
}

@media (min-width: 992px) {
    .container-fluid {
        width: 100%;
        padding: 2rem;
    }
}

@media (max-width: 991.98px) {
    .container-fluid {
        width: 100%;
        padding: 1rem;
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

.current-value {
    font-size: 2.5rem;
    font-weight: 600;
    color: var(--gradient-start);
}

.badge {
    padding: 0.5rem 1rem;
    border-radius: 2rem;
    font-weight: 500;
}

.btn-group {
    box-shadow: 0 2px 4px var(--shadow-color);
    border-radius: 0.5rem;
    overflow: hidden;
}

.btn-light {
    border: none;
    background: white;
    color: var(--text-secondary);
    transition: all 0.3s ease;
}

.btn-light:hover,
.btn-light.active {
    background: var(--gradient-mix);
    color: white;
}

.chart-container {
    margin: 1rem 0;
}

.stat-item {
    padding: 1rem;
    border-radius: 1rem;
    background: rgba(255, 255, 255, 0.5);
}

/* Fix for content being hidden under navbar */
.content-wrapper {
    width: 100%;
}

/* Ensure cards don't get hidden */
.row.g-4 {
    margin-top: 0;
    width: 100%;
}
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
let chart;
let currentRange = 'day';
let chartType = 'line';

document.addEventListener('DOMContentLoaded', function() {
    initializeChart();
    loadData();

    // Range buttons
    document.querySelectorAll('[data-range]').forEach(button => {
        button.addEventListener('click', function() {
            document.querySelectorAll('[data-range]').forEach(btn => btn.classList.remove('active'));
            this.classList.add('active');
            currentRange = this.dataset.range;
            loadData();
        });
    });

    // Chart type buttons
    document.querySelectorAll('[data-chart-type]').forEach(button => {
        button.addEventListener('click', function() {
            document.querySelectorAll('[data-chart-type]').forEach(btn => btn.classList.remove('active'));
            this.classList.add('active');
            chartType = this.dataset.chart_type;
            updateChartType();
        });
    });
});

function initializeChart() {
    const ctx = document.getElementById('pinChart').getContext('2d');
    chart = new Chart(ctx, {
        type: chartType,
        data: {
            labels: [],
            datasets: [{
                label: '{{ $pin->name }}',
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

function loadData() {
    fetch(`/api/pins/{{ $pin->id }}/chart-data?range=${currentRange}`)
        .then(response => response.json())
        .then(data => {
            updateChart(data);
            updateStats(data);
        })
        .catch(error => console.error('Error loading data:', error));
}

function updateChart(data) {
    chart.data.labels = data.timestamps;
    chart.data.datasets[0].data = data.values;
    chart.update();
}

function updateStats(data) {
    document.getElementById('avgValue').textContent = data.stats.avg.toFixed(2);
    document.getElementById('minValue').textContent = data.stats.min.toFixed(2);
    document.getElementById('maxValue').textContent = data.stats.max.toFixed(2);
}

function updateChartType() {
    chart.config.type = chartType;
    chart.update();
}

function refreshData() {
    loadData();
}

// WebSocket connection for real-time updates
const ws = new WebSocket(`ws://${window.location.hostname}:6001`);
ws.onmessage = function(event) {
    const data = JSON.parse(event.data);
    if (data.pin_id === {{ $pin->id }}) {
        document.getElementById('currentValue').textContent = data.value;
        document.getElementById('lastUpdate').textContent = 'Just now';
    }
};
</script>
@endpush 