<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Device;
use App\Models\Project;

class DeviceSeeder extends Seeder
{
    public function run(): void
    {
        // Get all projects
        $projects = Project::all();

        foreach ($projects as $project) {
            // Create 2 devices for each project
            for ($i = 1; $i <= 2; $i++) {
                Device::create([
                    'user_id' => $project->user_id, // Add user_id from the project
                    'project_id' => $project->id,
                    'name' => "Device {$i} - {$project->name}",
                    'description' => "Test device {$i} for {$project->name}",
                    'device_key' => md5(uniqid(rand(), true)), // Generate random device key
                    'is_online' => false,
                    'last_online' => now()
                ]);
            }
        }
    }
} 