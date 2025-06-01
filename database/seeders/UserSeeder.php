<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Create single user
        User::create([
            'name' => 'Agung',
            'email' => 'agung@gmail.com',
            'password' => Hash::make('agung123'),
            'email_verified_at' => now()
        ]);
    }
} 