export class WebSocketEventHandler {
    constructor(wsManager) {
        this.wsManager = wsManager;
    }

    setupEventListeners(socket) {
        socket.onopen = this.handleOpen.bind(this);
        socket.onclose = this.handleClose.bind(this);
        socket.onerror = this.handleError.bind(this);
        socket.onmessage = this.handleMessage.bind(this);
    }

    handleOpen() {
        console.log('WebSocket Connected');
        this.wsManager.isConnected = true;
        this.wsManager.reconnectAttempts = 0;
        this.wsManager.authenticate();
    }

    handleClose(event) {
        console.log('WebSocket Disconnected:', event);
        this.wsManager.isConnected = false;
        this.wsManager.handleReconnect();
    }

    handleError(error) {
        console.error('WebSocket Error:', error);
        this.wsManager.isConnected = false;
    }

    handleMessage(event) {
        try {
            const data = JSON.parse(event.data);
            console.log('WebSocket message received:', data);
            this.wsManager.handleMessage(data);
        } catch (error) {
            console.error('Error parsing WebSocket message:', error);
        }
    }
} 