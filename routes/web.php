<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\DeviceController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PinController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TelegramController;
use App\Http\Controllers\ChartController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Basic Routes
Route::get('/', function () {
    return view('welcome');
});

// Authentication Routes
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Registration Routes
Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [RegisterController::class, 'register']);

// Protected Routes
Route::middleware(['auth'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Project Management
    Route::resource('projects', ProjectController::class);
    
    // Device Management
    Route::resource('devices', DeviceController::class);
    Route::get('/devices/{device}/code', [DeviceController::class, 'generateCode'])->name('devices.code');
    Route::get('/devices/{device}/configure', [DeviceController::class, 'configure'])->name('devices.configure');
    Route::post('/api/devices/{device}/configure', [DeviceController::class, 'saveConfiguration'])->name('devices.save-configuration');
    
    // Pin Management
    Route::get('/pins', [PinController::class, 'index'])->name('pins.index');
    Route::get('/pins/chart', [PinController::class, 'charts'])->name('pins.charts');
    Route::get('/devices/{device}/pins/create', [PinController::class, 'create'])->name('pins.create');
    Route::post('/devices/{device}/pins', [PinController::class, 'store'])->name('pins.store');
    Route::get('/devices/{device}/pins/{pin}/edit', [PinController::class, 'edit'])->name('pins.edit');
    Route::put('/devices/{device}/pins/{pin}', [PinController::class, 'update'])->name('pins.update');
    Route::delete('/devices/{device}/pins/{pin}', [PinController::class, 'destroy'])->name('pins.destroy');
    Route::post('/devices/{device}/pins/{pin}/status', [PinController::class, 'updateStatus'])->name('pins.update-status');
    Route::get('/devices/{device}/pins/{pin}/chart', [PinController::class, 'chart'])->name('pins.chart');

    // User Profile Management
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/2fa/enable', [ProfileController::class, 'enableTwoFactor'])->name('profile.2fa.enable');
    Route::post('/profile/2fa/disable', [ProfileController::class, 'disableTwoFactor'])->name('profile.2fa.disable');
    Route::post('/profile/logout-other-devices', [ProfileController::class, 'logoutOtherDevices'])->name('profile.logout-other-devices');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Telegram Routes
    Route::post('/telegram/test-connection/{chatId}', [TelegramController::class, 'testConnection'])
        ->name('telegram.test-connection');

    // Chart routes
    Route::get('/charts', [ChartController::class, 'index'])->name('charts.index');
    Route::get('/charts/data/{pin}', [ChartController::class, 'getData'])->name('charts.data');
});
