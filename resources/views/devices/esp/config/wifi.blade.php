// WiFi Configuration
const char* ssid = "{{ $device->wifi_ssid ?: 'YOUR_WIFI_SSID' }}";
const char* password = "{{ $device->wifi_password ?: 'YOUR_WIFI_PASSWORD' }}";

// WiFi status check interval
const unsigned long WIFI_CHECK_INTERVAL = 5000;  // 5 seconds

// WiFi Reconnection interval
const unsigned long WIFI_RECONNECT_INTERVAL = 30000;      // 30 seconds 