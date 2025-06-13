<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProjectController extends Controller
{
    public function index()
    {
        $projects = auth()->user()->projects()
            ->withCount('devices')
            ->with(['devices' => function($query) {
                $query->orderByDesc(
                    DB::raw('GREATEST(created_at, updated_at)')
                );
            }])
            ->get()
            ->map(function($project) {
                // Get the latest device activity time (either creation or update)
                $latestDevice = $project->devices->first();
                $latestDeviceTime = $latestDevice ? max($latestDevice->created_at, $latestDevice->updated_at) : null;
                
                // Set effective_updated_at to the latest between project update and device activity
                $project->effective_updated_at = $latestDeviceTime && $latestDeviceTime > $project->updated_at 
                    ? $latestDeviceTime 
                    : $project->updated_at;
                
                return $project;
            });

        return view('projects.index', compact('projects'));
    }

    public function create()
    {
        return view('projects.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $project = auth()->user()->projects()->create($validated);

        return redirect()->route('projects.show', $project)
            ->with('success', 'Project created successfully.');
    }

    public function show(Project $project)
    {
        if (auth()->id() !== $project->user_id) {
            abort(403);
        }
        
        $project->load(['devices' => function($query) {
            $query->orderByDesc(
                DB::raw('GREATEST(created_at, updated_at)')
            );
        }]);

        // Calculate effective_updated_at
        $latestDevice = $project->devices->first();
        $latestDeviceTime = $latestDevice ? max($latestDevice->created_at, $latestDevice->updated_at) : null;
        $project->effective_updated_at = $latestDeviceTime && $latestDeviceTime > $project->updated_at 
            ? $latestDeviceTime 
            : $project->updated_at;

        return view('projects.show', compact('project'));
    }

    public function edit(Project $project)
    {
        if (auth()->id() !== $project->user_id) {
            abort(403);
        }

        return view('projects.edit', compact('project'));
    }

    public function update(Request $request, Project $project)
    {
        if (auth()->id() !== $project->user_id) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string'
        ]);

        $project->update($validated);

        return redirect()->route('projects.show', $project)
            ->with('success', 'Project updated successfully.');
    }

    public function destroy(Project $project)
    {
        if (auth()->id() !== $project->user_id) {
            abort(403);
        }

        $project->delete();

        return redirect()->route('projects.index')
            ->with('success', 'Project deleted successfully.');
    }
} 