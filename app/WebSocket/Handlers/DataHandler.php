<?php

namespace App\WebSocket\Handlers;

use Workerman\Connection\TcpConnection;
use App\Models\Device;
use App\WebSocket\Traits\WebSocketMessageHandlerTrait;
use App\WebSocket\WebSocketHandler;

class DataHandler
{
    use WebSocketMessageHandlerTrait;

    protected $webSocketHandler;

    public function __construct(WebSocketHandler $webSocketHandler)
    {
        $this->webSocketHandler = $webSocketHandler;
    }

    public function handle(TcpConnection $from, array $data)
    {
        if (isset($data['device_key'])) {
            $device = Device::where('device_key', $data['device_key'])->first();
            if ($device) {
                foreach ($device->pins as $pin) {
                    $this->broadcastPinConfig($device, $pin);
                }
            }
        }
    }

    public function handlePinControl(TcpConnection $from, array $data, array $deviceConnections, array $clients)
    {
        $deviceKey = isset($data['device_key']) ? $data['device_key'] : null;
        
        if (isset($deviceConnections[$deviceKey])) {
            $deviceConn = $deviceConnections[$deviceKey];
            $device = Device::where('device_key', $deviceKey)->first();
            
            if ($device && $device->is_online) {
                try {
                    $deviceConn->send(json_encode($data));
                    
                    if (isset($clients[$from->id])) {
                        $from->send(json_encode([
                            'status' => 'success',
                            'message' => 'Pin control message sent successfully'
                        ]));
                    }
                    return true;
                } catch (\Exception $e) {
                    if (isset($clients[$from->id])) {
                        $from->send(json_encode([
                            'status' => 'error',
                            'message' => 'Failed to send pin control message'
                        ]));
                    }
                }
            } else {
                if (isset($clients[$from->id])) {
                    $from->send(json_encode([
                        'status' => 'error',
                        'message' => 'Device is offline'
                    ]));
                }
            }
        } else {
            if (isset($clients[$from->id])) {
                $from->send(json_encode([
                    'status' => 'error',
                    'message' => 'Device not connected'
                ]));
            }
        }
        return false;
    }
} 