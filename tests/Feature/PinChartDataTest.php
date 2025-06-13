<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Project;
use App\Models\Device;
use App\Models\Pin;
use App\Models\PinLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;
use Tests\TestCase;

class PinChartDataTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_fetch_pin_chart_data()
    {
        $user = User::factory()->create();

        $project = Project::create([
            'user_id' => $user->id,
            'name' => 'Test Project',
            'description' => 'Project for testing',
        ]);

        $device = Device::create([
            'project_id' => $project->id,
            'name' => 'Test Device',
            'description' => 'Device for testing',
            'device_key' => 'test-key',
            'is_online' => false,
            'last_online' => now(),
        ]);
        // assign user id separately as it's not mass assignable
        $device->user_id = $user->id;
        $device->save();

        $pin = Pin::create([
            'device_id' => $device->id,
            'name' => 'Test Pin',
            'pin_number' => 1,
            'type' => 'analog_input',
            'is_active' => true,
        ]);

        foreach (range(1, 3) as $i) {
            PinLog::create([
                'pin_id' => $pin->id,
                'value' => $i,
                'raw_value' => $i * 10,
                'created_at' => Carbon::now()->subMinutes(3 - $i),
                'updated_at' => Carbon::now()->subMinutes(3 - $i),
            ]);
        }

        $response = $this->actingAs($user)->getJson("/api/pins/{$pin->uuid}/chart-data");

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'timestamps',
                     'values',
                     'stats',
                 ]);
    }
}
