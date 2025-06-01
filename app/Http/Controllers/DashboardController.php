<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Device;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        
        // Get projects with device counts
        $projects = $user->projects()
            ->withCount('devices')
            ->latest()
            ->take(6)
            ->get();

        // Get devices with their projects and pins
        $devices = Device::with(['project', 'pins'])
            ->whereHas('project', function($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->latest()
            ->get();

        // Calculate statistics
        $totalProjects = $user->projects()->count();
        $totalDevices = $devices->count();
        $onlineDevices = $devices->where('is_online', true)->count();

        return view('dashboard', compact(
            'projects',
            'devices',
            'totalProjects',
            'totalDevices',
            'onlineDevices'
        ));
    }
} 