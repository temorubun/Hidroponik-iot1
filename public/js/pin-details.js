let chart;
let selectedRange = 'hour'; // Default to hour view
let isPolling = false;

document.addEventListener('DOMContentLoaded', function() {
    initializeChart();
    loadData();
    startPolling();

    // Time range buttons
    document.querySelectorAll('[data-time-range]').forEach(button => {
        button.addEventListener('click', function() {
            document.querySelectorAll('[data-time-range]').forEach(btn => btn.classList.remove('active'));
            this.classList.add('active');
            selectedRange = this.dataset.timeRange;
            loadData();
        });
    });

    // Toggle button for digital pins
    const toggleButton = document.querySelector('.toggle-pin');
    if (toggleButton) {
        toggleButton.addEventListener('click', function() {
            const pinId = this.dataset.pinId;
            const deviceId = this.dataset.deviceId;
            
            this.disabled = true;
            this.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Processing...';
            
            fetch(`/devices/${deviceId}/pins/${pinId}/status`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            })
            .then(response => response.json())
            .then(data => {
                this.disabled = false;
                this.innerHTML = '<i class="fas fa-power-off me-2"></i>Toggle Pin';
                
                if (data.success) {
                    showSuccess();
                    loadData(); // Reload data after successful toggle
                } else {
                    showError('Failed to toggle pin');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                this.disabled = false;
                this.innerHTML = '<i class="fas fa-power-off me-2"></i>Toggle Pin';
                showError('Failed to toggle pin');
            });
        });
    }
});

function initializeChart() {
    const ctx = document.getElementById('pinChart').getContext('2d');
    chart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: [],
            datasets: [{
                label: 'Pin Value',
                data: [],
                borderColor: 'rgb(75, 192, 192)',
                tension: 0.1,
                fill: false
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                x: {
                    type: 'time',
                    time: {
                        unit: 'minute'
                    },
                    title: {
                        display: true,
                        text: 'Time'
                    }
                },
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: pinType === 'ph_sensor' ? 'pH Value' : 'Value'
                    }
                }
            },
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            let label = context.dataset.label || '';
                            if (label) {
                                label += ': ';
                            }
                            if (pinType === 'digital_output') {
                                label += context.parsed.y === 1 ? 'ON' : 'OFF';
                            } else if (pinType === 'ph_sensor') {
                                label += context.parsed.y.toFixed(1) + ' pH';
                            } else {
                                label += context.parsed.y;
                            }
                            return label;
                        }
                    }
                }
            }
        }
    });
}

async function loadData() {
    try {
        setLoadingState(true);
        const response = await fetch(`/api/pins/${pinId}/chart-data?range=${selectedRange}`);
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        
        const data = await response.json();
        
        // Update chart
        chart.data.labels = data.timestamps;
        chart.data.datasets[0].data = data.values.map((value, index) => ({
            x: data.timestamps[index],
            y: value
        }));
        chart.update();

        // Update statistics
        updateStats(data.stats);
        
        // Update current value
        updateCurrentValue(data.values[data.values.length - 1]);
        
        hideError(); // Hide any existing error message
    } catch (error) {
        console.error('Error loading data:', error);
        showError('Failed to load data. Please check your connection.');
    } finally {
        setLoadingState(false);
    }
}

function updateStats(stats) {
    if (stats) {
        const formatValue = (value) => {
            if (pinType === 'digital_output') {
                return value === 1 ? 'ON' : 'OFF';
            } else if (pinType === 'ph_sensor') {
                return value.toFixed(1) + ' pH';
            }
            return value.toFixed(2);
        };

        document.getElementById('avgValue').textContent = formatValue(stats.avg);
        document.getElementById('minValue').textContent = formatValue(stats.min);
        document.getElementById('maxValue').textContent = formatValue(stats.max);
    }
}

function updateCurrentValue(value) {
    if (value !== undefined) {
        const element = document.getElementById('currentValue');
        if (pinType === 'digital_output') {
            element.textContent = value === 1 ? 'ON' : 'OFF';
        } else if (pinType === 'ph_sensor') {
            element.textContent = value.toFixed(1) + ' pH';
        } else {
            element.textContent = value.toFixed(2);
        }
    }
}

function setLoadingState(isLoading) {
    const chartContainer = document.querySelector('.chart-container');
    if (isLoading) {
        chartContainer.style.opacity = '0.5';
        chartContainer.style.pointerEvents = 'none';
    } else {
        chartContainer.style.opacity = '1';
        chartContainer.style.pointerEvents = 'auto';
    }
}

function showError(message) {
    let errorDiv = document.getElementById('chart-error');
    if (!errorDiv) {
        errorDiv = document.createElement('div');
        errorDiv.id = 'chart-error';
        errorDiv.className = 'alert alert-danger mt-3';
        document.querySelector('.chart-container').after(errorDiv);
    }
    errorDiv.textContent = `Error: ${message}`;
    errorDiv.style.display = 'block';
}

function hideError() {
    const errorDiv = document.getElementById('chart-error');
    if (errorDiv) {
        errorDiv.style.display = 'none';
    }
}

function showSuccess() {
    let successDiv = document.getElementById('chart-success');
    if (!successDiv) {
        successDiv = document.createElement('div');
        successDiv.id = 'chart-success';
        successDiv.className = 'alert alert-success mt-3';
        document.querySelector('.chart-container').after(successDiv);
    }
    successDiv.textContent = 'Pin toggled successfully!';
    successDiv.style.display = 'block';
    setTimeout(() => {
        successDiv.style.display = 'none';
    }, 3000);
}

function startPolling() {
    if (!isPolling) {
        isPolling = true;
        // Poll every 5 seconds
        setInterval(() => {
            if (document.visibilityState === 'visible') {
                loadData();
            }
        }, 5000);
    }
}

// Handle page visibility changes
document.addEventListener('visibilitychange', function() {
    if (document.visibilityState === 'visible') {
        loadData(); // Immediately load data when page becomes visible
    }
}); 