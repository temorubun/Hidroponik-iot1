let ws;

function initWebSocket(deviceKey, wsUrl) {
    console.log('[WebSocket] Configuration:', {
        url: wsUrl,
        deviceKey: deviceKey
    });
    
    if (ws) {
        console.log('[WebSocket] Closing existing connection...');
        ws.close();
    }

    ws = new WebSocket(wsUrl);

    ws.onopen = function() {
        console.log('[WebSocket] Connected to server');
        const authMessage = {
            type: 'web_auth',
            device_key: deviceKey
        };
        console.log('[WebSocket] Sending auth message:', authMessage);
        ws.send(JSON.stringify(authMessage));
    };

    ws.onmessage = function(event) {
        try {
            const data = JSON.parse(event.data);
            console.log('[WebSocket] Received message:', {
                type: data.type,
                data: data
            });

            if (data.type === 'device_status') {
                console.log('[WebSocket] Updating device status:', data);
                updateDeviceStatus(data.is_online, data.ip_address);
            }
            else if (data.type === 'pin_update') {
                console.log('[WebSocket] Updating pin status:', data);
                updatePinStatus(data.pin_number, data.value);
            }
            else if (data.type === 'sensor_update') {
                console.log('[WebSocket] Updating sensor data:', data);
                const pinElement = document.getElementById('value-' + data.pin.id);
                if (pinElement) {
                    if (pinElement.classList.contains('ph-value')) {
                        pinElement.textContent = formatPhValue(data.pin.value);
                        const timestampElement = document.getElementById('timestamp-' + data.pin.id);
                        if (timestampElement) {
                            timestampElement.textContent = 'Last update: ' + new Date(data.timestamp).toLocaleString();
                        }
                    } else {
                        pinElement.textContent = data.pin.value;
                    }
                } else {
                    console.warn('[WebSocket] Element not found for pin:', data.pin.id);
                }
            }
            else if (data.type === 'pin_configured') {
                console.log('[WebSocket] Pin configuration confirmed:', data);
                if (data.status === 'success') {
                    console.log('[WebSocket] Pin configured successfully');
                } else {
                    alert('Failed to configure pin on device');
                }
            }
            else if (data.status === 'error') {
                console.error('[WebSocket] Error from server:', data.message);
                notifications.error(data.message);
                document.querySelectorAll('.form-check-input:disabled').forEach(input => {
                    input.disabled = false;
                    input.checked = !input.checked;
                });
            }
        } catch (error) {
            console.error('[WebSocket] Error parsing message:', error, 'Raw:', event.data);
        }
    };

    ws.onclose = function(event) {
        console.log('[WebSocket] Connection closed:', {
            code: event.code,
            reason: event.reason,
            wasClean: event.wasClean
        });
        setTimeout(() => initWebSocket(deviceKey, wsUrl), 5000);
    };

    ws.onerror = function(error) {
        console.error('[WebSocket] Error:', error);
    };
}

function formatPhValue(value) {
    return parseFloat(value).toFixed(1) + ' pH';
}

function updateDeviceStatus(isOnline, ipAddress) {
    const statusBadge = document.querySelector('.device-status-badge');
    const ipText = document.querySelector('#device-ip-text');
    
    if (statusBadge) {
        statusBadge.className = `badge ${isOnline ? 'bg-success' : 'bg-danger'} device-status-badge`;
        statusBadge.innerHTML = `
            <i class="fas ${isOnline ? 'fa-check-circle' : 'fa-times-circle'} me-1"></i>
            ${isOnline ? 'Online' : 'Offline'}
        `;
    }

    if (ipText) {
        ipText.textContent = isOnline ? ipAddress : 'Not Connected';
    }
}

function updatePinStatus(pinNumber, value) {
    const pinElement = document.querySelector(`[data-pin-number="${pinNumber}"]`);
    if (pinElement) {
        const statusElement = pinElement.querySelector('.pin-status');
        if (statusElement) {
            statusElement.textContent = value;
        }

        const toggleButton = pinElement.querySelector('.pin-toggle');
        if (toggleButton) {
            toggleButton.checked = value === '1' || value === 1;
        }
    }
}

function updatePinValue(pinId, value) {
    const pin = document.getElementById(`pin-${pinId}`).closest('[data-pin-number]');
    if (!pin) {
        console.error('[ERROR] Could not find pin element');
        return;
    }
    
    const pinNumber = parseInt(pin.dataset.pinNumber);
    if (isNaN(pinNumber)) {
        console.error('[ERROR] Invalid pin number');
        return;
    }
    
    console.log('[PIN] Updating pin:', {
        pinId: pinId,
        pinNumber: pinNumber,
        value: value,
        deviceKey: deviceKey
    });
    
    const input = document.getElementById(`pin-${pinId}`);
    input.disabled = true;

    if (!ws || ws.readyState !== WebSocket.OPEN) {
        console.error('[ERROR] WebSocket not connected');
        notifications.error('Error: WebSocket not connected. Please refresh the page.');
        input.disabled = false;
        input.checked = !value;
        return;
    }

    const message = {
        type: 'pin',
        device_key: deviceKey,
        pin: pinNumber,
        value: value ? 1 : 0
    };

    try {
        ws.send(JSON.stringify(message));
        console.log('[WebSocket] Sent pin control message:', message);
        
        const label = document.querySelector(`label[for="pin-${pinId}"]`);
        if (label) {
            label.textContent = value ? 'ON' : 'OFF';
        }
        
        setTimeout(() => {
            input.disabled = false;
        }, 500);
    } catch (error) {
        console.error('[WebSocket] Error sending message:', error);
        notifications.error('Failed to send command to device');
        input.disabled = false;
        input.checked = !value;
    }
}

function copyDeviceKey() {
    const deviceKey = document.getElementById('device_key').value;
    navigator.clipboard.writeText(deviceKey).then(() => {
        notifications.success('Device key copied to clipboard!');
    });
}

function togglePasswordVisibility() {
    const password = document.getElementById('wifi-password');
    const icon = document.getElementById('password-toggle-icon');
    
    if (password.style.filter === 'none') {
        password.style.filter = 'blur(4px)';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    } else {
        password.style.filter = 'none';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    }
}

function addNewPin() {
    window.location.href = pinCreateUrl;
}

function rebootESP() {
    if (!ws || ws.readyState !== WebSocket.OPEN) {
        notifications.warning('WebSocket connection not ready. Please try again.');
        return;
    }

    const rebootBtn = document.getElementById('rebootBtn');
    rebootBtn.disabled = true;
    rebootBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Rebooting...';

    const rebootMessage = {
        type: 'reboot',
        device_key: deviceKey
    };

    try {
        ws.send(JSON.stringify(rebootMessage));
        console.log('[WebSocket] Sent reboot command');
        
        setTimeout(() => {
            rebootBtn.disabled = false;
            rebootBtn.innerHTML = '<i class="fas fa-sync"></i> Reboot ESP';
        }, 5000);
    } catch (error) {
        console.error('[WebSocket] Error sending reboot command:', error);
        notifications.error('Failed to send reboot command');
        rebootBtn.disabled = false;
        rebootBtn.innerHTML = '<i class="fas fa-sync"></i> Reboot ESP';
    }
}

function configurePinOnDevice(pinId) {
    axios.post(`/api/pins/${pinId}/configure`)
        .then(response => {
            if (response.data.success) {
                // Handle success
                location.reload();
            } else {
                notifications.error('Failed to configure pin on device');
            }
        })
        .catch(error => {
            if (error.response && error.response.data && error.response.data.message) {
                notifications.error(error.response.data.message);
            } else {
                notifications.error('Failed to configure pin on device');
            }
        });
}

function sendCommand() {
    if (!ws || ws.readyState !== WebSocket.OPEN) {
        notifications.error('Error: WebSocket not connected. Please refresh the page.');
        return;
    }
    // ... rest of the function
}

function sendRebootCommand() {
    if (!ws || ws.readyState !== WebSocket.OPEN) {
        notifications.warning('WebSocket connection not ready. Please try again.');
        return;
    }

    const command = {
        type: 'reboot',
        device_id: deviceId
    };

    try {
        ws.send(JSON.stringify(command));
    } catch (error) {
        notifications.error('Failed to send reboot command');
        console.error('WebSocket Error:', error);
    }
}

// Initialize when document is ready
document.addEventListener('DOMContentLoaded', function() {
    initWebSocket(deviceKey, wsUrl);
    
    document.querySelectorAll('.ph-value').forEach(function(element) {
        const value = parseFloat(element.textContent);
        if (!isNaN(value)) {
            element.textContent = formatPhValue(value);
        }
    });

    const password = document.getElementById('wifi-password');
    if (password) {
        password.style.filter = 'blur(4px)';
    }
}); 