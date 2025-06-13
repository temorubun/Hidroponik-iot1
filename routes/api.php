<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\DeviceApiController;
use App\Http\Controllers\WebSocketController;
use App\Http\Controllers\SensorController;
use App\Http\Controllers\PinController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group.
|
*/

// Device Status Update
Route::post('/devices/status', [DeviceApiController::class, 'updateStatus']);

// Device Data Routes
Route::get('/devices/{device}', [DeviceApiController::class, 'show']);
Route::get('/devices/{device}/pins', [DeviceApiController::class, 'pins']);

// Pin Routes
Route::get('/pins/{pin}/chart-data', [PinController::class, 'chartData']);
Route::post('/devices/{device}/pins/{pin}/status', [PinController::class, 'updateStatus']);

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Sensor Data Routes
Route::post('/sensor-data', [SensorController::class, 'sendData']);
Route::get('/sensor-stream', [SensorController::class, 'stream']);

// ESP32 Heartbeat endpoint
Route::post('/esp32/heartbeat', [SensorController::class, 'heartbeat']); 