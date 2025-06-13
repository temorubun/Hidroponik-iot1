<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Project;
use App\Models\User;

class ProjectSeeder extends Seeder
{
    public function run(): void
    {
        // Get all users
        $users = User::all();

        foreach ($users as $user) {
            // Create 2 projects for each user
            Project::create([
                'user_id' => $user->id,
                'name' => 'Hydroponic System',
                'description' => 'NFT hydroponic system with automated pH and water level control'
            ]);

            Project::create([
                'user_id' => $user->id,
                'name' => 'Aquaponic System',
                'description' => 'Integrated aquaponic system with fish tank monitoring'
            ]);
        }
    }
} 