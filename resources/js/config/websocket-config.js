export const WS_CONFIG = {
    host: window.WS_HOST || window.location.hostname,
    port: window.WS_PORT || 6001,
    path: window.WS_PATH || '/',
    reconnectAttempts: 10,
    reconnectInterval: 5000
}; 