class WebSocketClient {
    constructor() {
        this.socket = null;
        this.connected = false;
        this.reconnectAttempts = 0;
        this.maxReconnectAttempts = 5;
        this.reconnectDelay = 5000;
    }

    connect() {
        try {
            this.socket = new WebSocket('ws://' + window.location.hostname + ':6001');

            this.socket.onopen = () => {
                console.log('Connected to WebSocket server');
                this.connected = true;
                this.reconnectAttempts = 0;
            };

            this.socket.onmessage = (event) => {
                console.log('Received message:', event.data);
                try {
                    const data = JSON.parse(event.data);
                    this.handleMessage(data);
                } catch (e) {
                    console.error('Error parsing message:', e);
                }
            };

            this.socket.onclose = () => {
                console.log('Disconnected from WebSocket server');
                this.connected = false;
                this.reconnect();
            };

            this.socket.onerror = (error) => {
                console.error('WebSocket error:', error);
                this.connected = false;
            };
        } catch (error) {
            console.error('Connection error:', error);
            this.reconnect();
        }
    }

    reconnect() {
        if (this.reconnectAttempts < this.maxReconnectAttempts) {
            this.reconnectAttempts++;
            console.log(`Reconnecting... Attempt ${this.reconnectAttempts} of ${this.maxReconnectAttempts}`);
            setTimeout(() => this.connect(), this.reconnectDelay);
        } else {
            console.error('Max reconnection attempts reached');
        }
    }

    handleMessage(data) {
        // Handle different types of messages
        switch(data.type) {
            case 'sensor_data':
                // Handle sensor data
                this.handleSensorData(data);
                break;
            case 'command':
                // Handle commands
                this.handleCommand(data);
                break;
            case 'ph_update':
                // Update pH value in real-time
                const phElement = document.querySelector(`[data-device-id="${data.device_id}"] .ph-value`);
                if (phElement) {
                    phElement.textContent = parseFloat(data.ph_value).toFixed(2);
                    phElement.dataset.timestamp = data.timestamp;
                }
                break;
            default:
                console.log('Unknown message type:', data.type);
        }
    }

    handleSensorData(data) {
        // Example: Update UI with sensor data
        const event = new CustomEvent('sensorData', { detail: data });
        document.dispatchEvent(event);
    }

    handleCommand(data) {
        // Example: Handle commands from server
        const event = new CustomEvent('command', { detail: data });
        document.dispatchEvent(event);
    }

    sendMessage(message) {
        if (this.connected && this.socket) {
            try {
                const messageString = typeof message === 'string' ? message : JSON.stringify(message);
                this.socket.send(messageString);
            } catch (e) {
                console.error('Error sending message:', e);
            }
        } else {
            console.warn('Not connected to WebSocket server');
        }
    }

    disconnect() {
        if (this.socket) {
            this.socket.close();
            this.connected = false;
        }
    }
} 