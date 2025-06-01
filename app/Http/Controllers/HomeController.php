<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class HomeController extends Controller
{
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $devices = auth()->user()->devices()->with('pins')->get();
        $projects = auth()->user()->projects()->withCount('devices')->latest()->take(3)->get();
        $totalDevices = $devices->count();
        $onlineDevices = $devices->where('is_online', true)->count();
        $totalProjects = auth()->user()->projects()->count();
        
        return view('dashboard', compact('devices', 'projects', 'totalDevices', 'onlineDevices', 'totalProjects'));
    }
} 