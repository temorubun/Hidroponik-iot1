export class DeviceStatusHandler {
    constructor() {
        this.deviceStatuses = new Map();
    }

    updateStatus(data) {
        try {
            console.log('Updating device status:', data);
            const deviceId = data.device_id;
            
            this.deviceStatuses.set(deviceId, {
                isOnline: data.is_online,
                lastOnline: data.last_online,
                ipAddress: data.ip_address
            });

            this.updateUI(deviceId, data);
            this.dispatchStatusEvent(deviceId, data);
        } catch (error) {
            console.error('Error updating device status:', error);
        }
    }

    updateUI(deviceId, data) {
        // Update status badge
        const statusBadges = document.querySelectorAll('.device-status-badge, .status-badge');
        statusBadges.forEach(badge => {
            badge.className = `badge ${data.is_online ? 'bg-success' : 'bg-danger'} device-status-badge`;
            badge.innerHTML = `
                <i class="fas ${data.is_online ? 'fa-check-circle' : 'fa-times-circle'} me-1"></i>
                ${data.is_online ? 'Online' : 'Offline'}
            `;
        });

        // Update IP address
        const ipText = document.querySelector('#device-ip-text');
        if (ipText) {
            ipText.textContent = data.is_online ? data.ip_address : 'Not Connected';
        }

        if (!data.is_online) {
            this.handleOfflineState(deviceId);
        } else {
            this.handleOnlineState(deviceId);
        }
    }

    handleOfflineState(deviceId) {
        this.resetInputs(deviceId);
        this.resetSensorValues(deviceId);
        this.updateControlStates(deviceId, true);
    }

    handleOnlineState(deviceId) {
        this.updateControlStates(deviceId, false);
    }

    resetInputs(deviceId) {
        document.querySelectorAll(`input[data-device-id="${deviceId}"], .form-check-input[type="checkbox"]`).forEach(input => {
            input.checked = false;
            input.disabled = true;
            input.value = "0";
            
            const label = document.querySelector(`label[for="${input.id}"]`);
            if (label) {
                label.textContent = 'OFF';
                label.classList.add('text-muted');
            }
        });
    }

    resetSensorValues(deviceId) {
        document.querySelectorAll(`[data-device-id="${deviceId}"] .value-text, [data-device-id="${deviceId}"] .pin-value`).forEach(element => {
            element.textContent = element.classList.contains('ph-value') ? '0.0 pH' : '0';
        });
    }

    updateControlStates(deviceId, disabled) {
        const rebootBtn = document.getElementById('rebootBtn');
        if (rebootBtn && rebootBtn.getAttribute('data-device-id') === deviceId) {
            rebootBtn.disabled = disabled;
            rebootBtn.classList.toggle('disabled', disabled);
            rebootBtn.title = disabled ? 'Cannot reboot when device is offline' : '';
        }

        document.querySelectorAll(`[data-device-id="${deviceId}"]`).forEach(element => {
            if (element.tagName === 'INPUT' && element.type === 'checkbox') {
                element.disabled = disabled;
                element.title = disabled ? 'Device is offline' : '';
            }
        });
    }

    dispatchStatusEvent(deviceId, status) {
        const event = new CustomEvent('deviceStatusChanged', {
            detail: { deviceId, status }
        });
        window.dispatchEvent(event);
    }

    isDeviceOnline(deviceId) {
        return this.deviceStatuses.get(deviceId)?.isOnline || false;
    }

    getDeviceStatus(deviceId) {
        return this.deviceStatuses.get(deviceId);
    }
} 