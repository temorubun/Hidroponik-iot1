export class SensorHandler {
    updateSensorData(data) {
        const sensorElement = document.querySelector(`#sensor-${data.pin.id}`);
        if (sensorElement) {
            sensorElement.querySelector('.value').textContent = data.pin.value;
            sensorElement.querySelector('.timestamp').textContent = data.timestamp;
        }
    }

    handleRebootStatus(data) {
        const deviceElement = document.querySelector(`#device-${data.device_id}`);
        if (deviceElement) {
            const statusElement = deviceElement.querySelector('.reboot-status');
            if (statusElement) {
                statusElement.textContent = data.message;
                setTimeout(() => {
                    statusElement.textContent = '';
                }, 5000);
            }
        }
    }
} 