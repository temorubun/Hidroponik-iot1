<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() === 'sqlite') {
            Schema::table('devices', function (Blueprint $table) {
                $table->unsignedBigInteger('user_id')->after('id')->nullable();
            });
            return;
        }

        Schema::table('devices', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->after('id')->nullable();
        });

        // Get the first user's ID (assuming it exists)
        $userId = DB::table('users')->first()->id ?? null;
        
        if ($userId) {
            // Update existing records
            DB::table('devices')->whereNull('user_id')->update(['user_id' => $userId]);
        }

        Schema::table('devices', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });

        // Make the column not nullable after setting values
        DB::statement('ALTER TABLE devices MODIFY user_id bigint unsigned NOT NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('devices', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
        });
    }
};
