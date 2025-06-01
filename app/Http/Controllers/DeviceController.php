<?php

namespace App\Http\Controllers;

use App\Models\Device;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Gate;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use App\Models\EspPin;
use App\Models\Pin;

class DeviceController extends Controller
{
    public function index()
    {
        $devices = auth()->user()->devices()->with('pins')->get();
        return view('devices.index', compact('devices'));
    }

    public function create()
    {
        $projects = auth()->user()->projects;
        return view('devices.create', compact('projects'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $device = auth()->user()->devices()->create([
            'project_id' => $validated['project_id'],
            'name' => $validated['name'],
            'description' => $validated['description'],
            'device_key' => Str::random(32),
        ]);

        return redirect()->route('devices.show', $device)
            ->with('success', 'Device created successfully.');
    }

    public function show(Device $device)
    {
        if (! Gate::allows('view', $device)) {
            abort(403);
        }
        
        $device->load('pins');
        return view('devices.show', compact('device'));
    }

    public function edit(Device $device)
    {
        if (! Gate::allows('update', $device)) {
            abort(403);
        }

        return view('devices.edit', compact('device'));
    }

    public function update(Request $request, Device $device)
    {
        if (! Gate::allows('update', $device)) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'wifi_ssid' => 'nullable|string|max:255',
            'wifi_password' => 'nullable|string|max:255',
            'wifi_qr_code' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Handle QR code upload
        if ($request->hasFile('wifi_qr_code')) {
            // Delete old QR code if exists
            if ($device->wifi_qr_code) {
                Storage::disk('public')->delete($device->wifi_qr_code);
            }
            
            $path = $request->file('wifi_qr_code')->store('wifi-qr-codes', 'public');
            $validated['wifi_qr_code'] = $path;
        }

        $device->update($validated);

        return redirect()->route('devices.show', $device)
            ->with('success', 'Device updated successfully.');
    }

    public function destroy(Device $device)
    {
        if (! Gate::allows('delete', $device)) {
            abort(403);
        }

        $device->delete();
        return redirect()->route('devices.index')
            ->with('success', 'Device deleted successfully.');
    }

    public function generateCode(Device $device)
    {
        if (! Gate::allows('view', $device)) {
            abort(403);
        }
        
        $device->load('pins');
        return view('devices.code', compact('device'));
    }

    public function configure(Device $device)
    {
        Gate::authorize('view', $device);
        
        $espPin = new EspPin();
        $availablePins = $espPin->getAvailablePins();
        
        return view('devices.configure', compact('device', 'availablePins'));
    }

    public function saveConfiguration(Request $request, Device $device)
    {
        Gate::authorize('update', $device);
        
        $validated = $request->validate([
            'wifi_ssid' => 'required|string|max:255',
            'wifi_password' => 'required|string|max:255',
            'pins' => 'array'
        ]);

        try {
            // Update device settings
            $device->wifi_ssid = $validated['wifi_ssid'];
            $device->wifi_password = $validated['wifi_password'];
            $device->save();

            // Update pin configurations
            if (!empty($validated['pins'])) {
                foreach ($validated['pins'] as $pinConfig) {
                    if (!isset($pinConfig['number'], $pinConfig['name'], $pinConfig['function'])) {
                        throw new \Exception('Invalid pin configuration format');
                    }

                    $pin = $device->pins()->where('pin_number', $pinConfig['number'])->first();
                    
                    if (!$pin) {
                        $pin = new Pin();
                        $pin->device_id = $device->id;
                        $pin->pin_number = $pinConfig['number'];
                    }
                    
                    $pin->name = $pinConfig['name'];
                    $pin->type = $pinConfig['function'];
                    $pin->is_active = $pinConfig['is_active'] ?? true;
                    $pin->save();
                }
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Configuration saved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to save configuration: ' . $e->getMessage()
            ], 500);
        }
    }
} 