import { WS_CONFIG } from './config/websocket-config.js';
import { DeviceStatusHandler } from './services/device-status-handler.js';
import { SensorHandler } from './services/sensor-handler.js';
import { WebSocketEventHandler } from './services/websocket-event-handler.js';
import { MessageHandler } from './services/message-handler.js';
import { ConnectionManager } from './services/connection-manager.js';

class WebSocketManager {
    constructor() {
        this.deviceStatusHandler = new DeviceStatusHandler();
        this.sensorHandler = new SensorHandler();
        this.messageHandler = new MessageHandler(this.deviceStatusHandler, this.sensorHandler);
        this.connectionManager = new ConnectionManager(WS_CONFIG);
        this.eventHandler = new WebSocketEventHandler(this);
        this.initialize();
    }

    initialize() {
        const socket = this.connectionManager.connect();
        if (socket) {
            this.eventHandler.setupEventListeners(socket);
        }
    }

    handleReconnect() {
        this.connectionManager.handleReconnect(() => this.initialize());
    }

    authenticate() {
        this.connectionManager.authenticate();
    }

    send(data) {
        this.connectionManager.send(data);
    }

    handleMessage(data) {
        this.messageHandler.handleMessage(data);
    }

    isDeviceOnline(deviceId) {
        return this.deviceStatusHandler.isDeviceOnline(deviceId);
    }

    getDeviceStatus(deviceId) {
        return this.deviceStatusHandler.getDeviceStatus(deviceId);
    }
}

// Initialize WebSocket connection when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    window.wsManager = new WebSocketManager();
}); 