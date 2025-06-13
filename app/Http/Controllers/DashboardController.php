<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Device;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        
        // Get projects with device counts and latest activity
        $projects = $user->projects()
            ->withCount('devices')
            ->with(['devices' => function($query) {
                $query->orderByDesc(
                    DB::raw('GREATEST(created_at, updated_at)')
                );
            }])
            ->latest()
            ->paginate(5, ['*'], 'projects_page')
            ->through(function($project) {
                // Get the latest device activity time (either creation or update)
                $latestDevice = $project->devices->first();
                $latestDeviceTime = $latestDevice ? max($latestDevice->created_at, $latestDevice->updated_at) : null;
                
                // Set effective_updated_at to the latest between project update and device activity
                $project->effective_updated_at = $latestDeviceTime && $latestDeviceTime > $project->updated_at 
                    ? $latestDeviceTime 
                    : $project->updated_at;
                
                return $project;
            });

        // Get devices with their projects and pins
        $devices = Device::with(['project', 'pins'])
            ->whereHas('project', function($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->latest()
            ->paginate(5, ['*'], 'devices_page');

        // Calculate statistics
        $totalProjects = $user->projects()->count();
        $totalDevices = Device::whereHas('project', function($query) use ($user) {
            $query->where('user_id', $user->id);
        })->count();
        $onlineDevices = Device::whereHas('project', function($query) use ($user) {
            $query->where('user_id', $user->id);
        })->where('is_online', true)->count();

        return view('dashboard', compact(
            'projects',
            'devices',
            'totalProjects',
            'totalDevices',
            'onlineDevices'
        ));
    }
} 