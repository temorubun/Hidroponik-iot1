<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Pusher\Pusher;

class WebSocketController extends Controller
{
    private $pusher;

    public function __construct()
    {
        $this->pusher = new Pusher(
            env('PUSHER_APP_KEY'),
            env('PUSHER_APP_SECRET'),
            env('PUSHER_APP_ID'),
            [
                'cluster' => env('PUSHER_APP_CLUSTER'),
                'useTLS' => true
            ]
        );
    }

    public function sendData(Request $request)
    {
        $data = $request->all();
        
        $this->pusher->trigger('esp32-channel', 'sensor-data', [
            'temperature' => $data['temperature'] ?? 0,
            'humidity' => $data['humidity'] ?? 0,
            'timestamp' => now()->toDateTimeString()
        ]);

        return response()->json(['status' => 'success']);
    }
} 