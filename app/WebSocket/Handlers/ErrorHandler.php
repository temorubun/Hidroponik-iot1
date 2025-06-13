<?php

namespace App\WebSocket\Handlers;

use Workerman\Connection\TcpConnection;
use App\Models\Device;

class ErrorHandler
{
    public function handle(TcpConnection $conn, $code, $msg)
    {
        if (isset($this->connectionToDevice[$conn->id])) {
            $device = $this->connectionToDevice[$conn->id];
            
            Device::where('id', $device->id)->update([
                'is_online' => false,
                'last_online' => now()
            ]);

            \Log::error("WebSocket error for device {$device->name}: {$msg}");
        } else {
            \Log::error("WebSocket error for unknown connection: {$msg}");
        }
        
        $conn->close();
    }

    public function handleMessageError($msg, $error)
    {
        \Log::error("Failed to parse WebSocket message:", [
            'error' => $error,
            'raw_message' => $msg
        ]);
    }
} 