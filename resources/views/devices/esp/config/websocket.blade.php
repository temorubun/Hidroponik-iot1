// WebSocket Configuration
const char* ws_host = "{{ request()->getHost() }}";
const uint16_t ws_port = {{ config('websocket.port', 6001) }};
const char* ws_path = "/";
const char* device_key = "{{ $device->device_key }}"; 