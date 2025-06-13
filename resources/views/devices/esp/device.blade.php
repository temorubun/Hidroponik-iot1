// ESP32 Code for {{ $device->name }}
#include &lt;WiFi.h&gt;
#include &lt;WebSocketsClient.h&gt;
#include &lt;ArduinoJson.h&gt;
#include &lt;map&gt;  // Add map header

WebSocketsClient webSocket;

// Include WiFi configuration
@include('devices.esp.config.wifi')

// Include WebSocket configuration
@include('devices.esp.config.websocket')

// Include intervals configuration
@include('devices.esp.config.intervals')

// Include pin structures
@include('devices.esp.pin.structures.pin')
@include('devices.esp.pin.structures.analog')

// Include pin functions
@include('devices.esp.pin.functions.validation')
@include('devices.esp.pin.functions.setup')
@include('devices.esp.pin.functions.cleanup')
@include('devices.esp.pin.digital.functions')
@include('devices.esp.pin.analog.functions')

// Include pH sensor header first
@include('devices.esp.sensor.ph.ph_sensor')

// Include pH sensor implementations
@include('devices.esp.sensor.ph.functions.reading')
@include('devices.esp.sensor.ph.functions.core')

// Include WebSocket handlers
@include('devices.esp.websocket.connection.manager')
@include('devices.esp.websocket.events.handler')
@include('devices.esp.websocket.handlers.message')
@include('devices.esp.websocket.handlers.pin')
@include('devices.esp.websocket.handlers.config')
@include('devices.esp.websocket.handlers.pin_config')
@include('devices.esp.websocket.handlers.cleanup')
@include('devices.esp.websocket.handlers.reboot')

// Include main setup and loop
@include('devices.esp.core.main')