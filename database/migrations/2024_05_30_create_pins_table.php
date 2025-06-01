<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        Schema::table('pins', function (Blueprint $table) {
            // First convert to varchar to avoid enum constraints
            DB::statement("ALTER TABLE pins MODIFY COLUMN type VARCHAR(50)");
            
            // Then update the type to include ph_sensor
            DB::statement("UPDATE pins SET type = 'ph_sensor' WHERE type = 'analog_input' AND name LIKE '%pH%'");
        });
    }

    public function down()
    {
        Schema::table('pins', function (Blueprint $table) {
            DB::statement("UPDATE pins SET type = 'analog_input' WHERE type = 'ph_sensor'");
            DB::statement("ALTER TABLE pins MODIFY COLUMN type ENUM('digital_input', 'digital_output', 'analog_input', 'analog_output') NOT NULL");
        });
    }
}; 