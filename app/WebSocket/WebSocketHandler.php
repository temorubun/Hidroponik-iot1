<?php

namespace App\WebSocket;

use Workerman\Connection\TcpConnection;
use App\Models\Device;
use App\Services\Device\DevicePingService;
use App\Services\WebSocket\WebSocketBroadcaster;
use App\WebSocket\Handlers\AuthHandler;
use App\WebSocket\Handlers\DataHandler;
use App\WebSocket\Handlers\DeviceHandler;
use App\WebSocket\Handlers\ErrorHandler;
use App\WebSocket\Handlers\MessageHandler;
use App\WebSocket\Handlers\SensorHandler;
use App\WebSocket\Traits\WebSocketConnectionTrait;
use App\WebSocket\Traits\WebSocketLoggingTrait;
use App\WebSocket\Traits\WebSocketMessageHandlerTrait;

class WebSocketHandler
{
    use WebSocketConnectionTrait;
    use WebSocketLoggingTrait;
    use WebSocketMessageHandlerTrait;

    protected $clients;
    protected $deviceConnections;
    protected $connectionToDevice;
    protected $deviceIPs;
    protected $devicePingService;

    protected $authHandler;
    protected $dataHandler;
    protected $deviceHandler;
    protected $errorHandler;
    protected $messageHandler;
    protected $sensorHandler;

    public function __construct(DevicePingService $devicePingService)
    {
        $this->clients = [];
        $this->deviceConnections = [];
        $this->connectionToDevice = [];
        $this->deviceIPs = [];
        $this->devicePingService = $devicePingService;

        $this->authHandler = new AuthHandler();
        $this->dataHandler = new DataHandler($this);
        $this->deviceHandler = new DeviceHandler();
        $this->errorHandler = new ErrorHandler();
        $this->messageHandler = new MessageHandler();
        $this->sensorHandler = new SensorHandler();

        // Initialize broadcaster with empty clients
        WebSocketBroadcaster::setClients($this->clients);
    }

    public function onOpen(TcpConnection $conn)
    {
        $this->clients[$conn->id] = $conn;
        WebSocketBroadcaster::setClients($this->clients);
    }

    public function onMessage(TcpConnection $from, $msg)
    {
        $data = $this->messageHandler->handle($from, $msg);
        if (!$data) return;

        if (isset($data['type'])) {
            switch ($data['type']) {
                case 'auth':
                    \Log::info("Processing device authentication", [
                        'client_ip' => $from->getRemoteIp(),
                        'data' => $data
                    ]);
                    
                    $device = $this->authHandler->handle($from, $data, $this->deviceConnections, $this->connectionToDevice, $this->deviceIPs);
                    if ($device) {
                        WebSocketBroadcaster::setDeviceIP($device->id, $from->getRemoteIp());
                        $this->devicePingService->startPinging($device, $from->getRemoteIp());
                        WebSocketBroadcaster::broadcastDeviceStatus($device);
                        
                        // Send any pending pin configurations
                        foreach ($device->pins as $pin) {
                            \Log::info("Sending pin configuration after auth", [
                                'device' => $device->name,
                                'pin' => $pin->pin_number,
                                'type' => $pin->type
                            ]);
                            $this->broadcastPinConfig($device, $pin);
                        }
                    }
                    break;

                case 'get_pins':
                    \Log::info("Device requesting pin configurations", [
                        'client_ip' => $from->getRemoteIp(),
                        'data' => $data
                    ]);
                    $this->dataHandler->handle($from, $data);
                    break;

                case 'pin':
                    $this->dataHandler->handlePinControl($from, $data, $this->deviceConnections, $this->clients);
                    break;

                case 'web_auth':
                    $device = $this->messageHandler->handleWebAuth($from, $data, $this->clients);
                    if ($device) {
                        // Broadcast status of all devices to the new web client
                        $devices = Device::all();
                        foreach ($devices as $dev) {
                            WebSocketBroadcaster::setDeviceIP($dev->id, 
                                isset($this->deviceIPs[$dev->id]) ? $this->deviceIPs[$dev->id] : null
                            );
                            WebSocketBroadcaster::broadcastDeviceStatus($dev);
                        }
                    }
                    break;

                case 'pong':
                    if (isset($data['device_key']) && isset($this->deviceConnections[$data['device_key']])) {
                        $device = Device::where('device_key', $data['device_key'])->first();
                        if ($device) {
                            $device->update(['last_online' => now()]);
                        }
                    }
                    break;

                case 'reboot':
                    if (isset($this->deviceConnections[$data['device_key']])) {
                        $device = Device::where('device_key', $data['device_key'])->first();
                        if ($this->deviceHandler->handleReboot($from, $data, $device, $this->deviceConnections, $this->connectionToDevice, $this->deviceIPs)) {
                            $this->devicePingService->stopPinging($device);
                            WebSocketBroadcaster::broadcastDeviceStatus($device);
                        }
                    }
                    break;

                case 'reboot_response':
                    $statusMessage = $this->messageHandler->handleRebootResponse($data);
                    if ($statusMessage) {
                        foreach ($this->clients as $client) {
                            $client->send(json_encode($statusMessage));
                        }
                    }
                    break;

                case 'sensor_data':
                    if (isset($this->connectionToDevice[$from->id])) {
                        $this->sensorHandler->handle($from, $data, $this->connectionToDevice[$from->id]);
                    }
                    break;
            }
        }
    }

    public function onClose(TcpConnection $conn)
    {
        if (isset($this->connectionToDevice[$conn->id])) {
            $device = $this->connectionToDevice[$conn->id];
            
            $this->devicePingService->stopPinging($device);

            WebSocketBroadcaster::removeDeviceIP($device->id);
            unset($this->deviceIPs[$device->id]);
            unset($this->deviceConnections[$device->device_key]);
            unset($this->connectionToDevice[$conn->id]);
            
            $this->logInfo("{$device->name} disconnected");
            WebSocketBroadcaster::broadcastDeviceStatus($device);
        }
        
        unset($this->clients[$conn->id]);
        WebSocketBroadcaster::setClients($this->clients);
    }

    public function onError(TcpConnection $conn, $code, $msg)
    {
        $this->errorHandler->handle($conn, $code, $msg);
    }
} 