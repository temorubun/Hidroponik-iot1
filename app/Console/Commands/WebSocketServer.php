<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Workerman\Worker;
use App\WebSocket\WebSocketHandler;

class WebSocketServer extends Command
{
    protected $signature = 'websocket:serve {--port=6001}';
    protected $description = 'Start the WebSocket server';

    public function handle()
    {
        $port = $this->option('port');
        $this->info("Starting WebSocket server on port {$port}...");
        
        // Create a Workerman server
        $ws_worker = new Worker("websocket://0.0.0.0:{$port}");
        
        // Set process count
        $ws_worker->count = 1;
        
        // Initialize WebSocket handler
        $handler = new WebSocketHandler();
        
        // Emitted when new connection come
        $ws_worker->onConnect = function($connection) use ($handler) {
            $handler->onOpen($connection);
        };
        
        // Emitted when data received
        $ws_worker->onMessage = function($connection, $data) use ($handler) {
            $handler->onMessage($connection, $data);
        };
        
        // Emitted when connection closed
        $ws_worker->onClose = function($connection) use ($handler) {
            $handler->onClose($connection);
        };
        
        // Handle errors
        $ws_worker->onError = function($connection, $code, $msg) use ($handler) {
            $handler->onError($connection, $code, $msg);
        };
        
        // Run worker
        Worker::runAll();
    }
} 