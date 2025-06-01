<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Doctrine\DBAL\Types\Type;

return new class extends Migration
{
    public function up()
    {
        // First, drop the enum constraint
        DB::statement("ALTER TABLE pins MODIFY COLUMN type VARCHAR(50)");
        
        // Then update existing records if needed
        DB::table('pins')->whereIn('type', ['digital_input', 'digital_output', 'analog_input'])->update([
            'type' => DB::raw('type') // Keep existing values
        ]);
    }

    public function down()
    {
        // Convert back to enum
        DB::statement("ALTER TABLE pins MODIFY COLUMN type ENUM('digital_input', 'digital_output', 'analog_input', 'ph_sensor')");
    }
}; 