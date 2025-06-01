@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h2 class="mb-0">Sensor Data Charts</h2>
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-secondary" onclick="updateTimeRange('24h')">24 Hours</button>
                        <button type="button" class="btn btn-secondary" onclick="updateTimeRange('7d')">7 Days</button>
                        <button type="button" class="btn btn-secondary" onclick="updateTimeRange('30d')">30 Days</button>
                    </div>
                </div>
                <div class="card-body">
                    @foreach($pins as $pin)
                    <div class="chart-container mb-4">
                        <h3>{{ $pin->name }} ({{ $pin->type }})</h3>
                        <div id="chart-{{ $pin->id }}" style="height: 300px;"></div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
let charts = {};
let currentTimeRange = '24h';

function initializeCharts() {
    @foreach($pins as $pin)
    charts[{{ $pin->id }}] = new ApexCharts(document.querySelector("#chart-{{ $pin->id }}"), {
        chart: {
            type: 'line',
            animations: {
                enabled: false
            },
            zoom: {
                enabled: true
            }
        },
        stroke: {
            curve: 'smooth',
            width: 2
        },
        series: [{
            name: '{{ $pin->name }}',
            data: []
        }],
        xaxis: {
            type: 'datetime'
        },
        yaxis: {
            title: {
                text: '{{ $pin->type === "ph_sensor" ? "pH Value" : "Value" }}'
            }
        },
        tooltip: {
            x: {
                format: 'dd MMM yyyy HH:mm:ss'
            }
        }
    });
    charts[{{ $pin->id }}].render();
    loadChartData({{ $pin->id }});
    @endforeach
}

function updateTimeRange(range) {
    currentTimeRange = range;
    Object.keys(charts).forEach(pinId => {
        loadChartData(pinId);
    });
}

function loadChartData(pinId) {
    fetch(`/charts/data/${pinId}?range=${currentTimeRange}`)
        .then(response => response.json())
        .then(data => {
            charts[pinId].updateSeries([{
                name: data.name,
                data: data.data
            }]);
        });
}

document.addEventListener('DOMContentLoaded', function() {
    initializeCharts();
    // Update every minute
    setInterval(() => {
        Object.keys(charts).forEach(pinId => {
            loadChartData(pinId);
        });
    }, 60000);
});
</script>
@endpush
@endsection 