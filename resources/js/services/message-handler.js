export class MessageHandler {
    constructor(deviceStatusHandler, sensorHandler) {
        this.deviceStatusHandler = deviceStatusHandler;
        this.sensorHandler = sensorHandler;
    }

    handleMessage(data) {
        try {
            console.log('Received message:', data);
            switch (data.type) {
                case 'device_status':
                    this.deviceStatusHandler.updateStatus(data);
                    break;
                    
                case 'sensor_update':
                    this.sensorHandler.updateSensorData(data);
                    break;
                    
                case 'reboot_status':
                    this.sensorHandler.handleRebootStatus(data);
                    break;

                default:
                    console.log('Unhandled message type:', data.type);
            }
        } catch (error) {
            console.error('Error handling WebSocket message:', error);
        }
    }
} 