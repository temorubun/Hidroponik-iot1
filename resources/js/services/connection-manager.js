export class ConnectionManager {
    constructor(config) {
        this.config = config;
        this.socket = null;
        this.isConnected = false;
        this.reconnectAttempts = 0;
    }

    connect() {
        if (this.socket && this.socket.readyState !== WebSocket.CLOSED) {
            console.log('WebSocket already connected or connecting');
            return null;
        }

        const wsUrl = `ws://${this.config.host}:${this.config.port}${this.config.path}`;
        console.log('Connecting to WebSocket:', wsUrl);
        
        try {
            this.socket = new WebSocket(wsUrl);
            return this.socket;
        } catch (error) {
            console.error('Error creating WebSocket connection:', error);
            return null;
        }
    }

    handleReconnect(callback) {
        if (this.reconnectAttempts < this.config.reconnectAttempts) {
            this.reconnectAttempts++;
            console.log(`Reconnecting attempt ${this.reconnectAttempts} of ${this.config.reconnectAttempts}...`);
            setTimeout(callback, this.config.reconnectInterval);
        } else {
            console.error('Max reconnection attempts reached');
        }
    }

    send(data) {
        if (!this.isConnected) {
            console.warn('Cannot send message: WebSocket not connected');
            return;
        }

        try {
            this.socket.send(JSON.stringify(data));
        } catch (error) {
            console.error('Error sending WebSocket message:', error);
            this.handleReconnect(() => this.connect());
        }
    }

    authenticate() {
        if (!this.isConnected) {
            console.warn('Cannot authenticate: WebSocket not connected');
            return;
        }

        this.send({
            type: 'web_auth',
            device_key: window.DEVICE_KEY
        });
    }
} 