<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Workerman\Worker;
use Workerman\Timer;
use App\WebSocket\WebSocketHandler;
use App\Services\Device\DevicePingService;

class WebSocketServer extends Command
{
    protected $signature = 'websocket:serve {action=start} {--d|daemon} {--g|graceful} {--port=6001}';
    protected $description = 'Start the WebSocket server';

    protected $devicePingService;

    public function __construct(DevicePingService $devicePingService)
    {
        parent::__construct();
        $this->devicePingService = $devicePingService;
    }

    public function handle()
    {
        $action = $this->argument('action');
        $port = $this->option('port');
        $daemon = $this->option('daemon');
        $graceful = $this->option('graceful');

        // Set runtime options for Workerman
        if ($daemon) {
            Worker::$daemonize = true;
        }
        
        if ($graceful) {
            Worker::$gracefulStop = true;
        }

        if ($action === 'start') {
            $this->startServer($port);
        } elseif ($action === 'stop') {
            Worker::stopAll();
        } elseif ($action === 'restart') {
            Worker::stopAll();
            $this->startServer($port);
        } elseif ($action === 'status') {
            // Status is handled automatically by Workerman
            Worker::runAll();
        } else {
            $this->error("Invalid action. Use: start, stop, restart, or status");
            return 1;
        }
    }

    protected function startServer($port)
    {
        $this->info("Starting WebSocket server on port {$port}...");
        
        // Create a Workerman server
        $ws_worker = new Worker("websocket://0.0.0.0:{$port}");
        
        // Set process count
        $ws_worker->count = 1;
        
        // Initialize WebSocket handler
        $handler = new WebSocketHandler($this->devicePingService);
        
        // Initialize Timer when worker starts
        $ws_worker->onWorkerStart = function($worker) {
            Timer::init();
        };
        
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