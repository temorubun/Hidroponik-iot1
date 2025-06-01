<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Modify users table
        Schema::table('users', function (Blueprint $table) {
            $table->uuid('uuid')->after('id')->unique();
        });

        // Modify projects table
        Schema::table('projects', function (Blueprint $table) {
            $table->uuid('uuid')->after('id')->unique();
        });

        // Modify devices table
        Schema::table('devices', function (Blueprint $table) {
            $table->uuid('uuid')->after('id')->unique();
        });

        // Modify pins table
        Schema::table('pins', function (Blueprint $table) {
            $table->uuid('uuid')->after('id')->unique();
        });

        // Modify pin_logs table
        Schema::table('pin_logs', function (Blueprint $table) {
            $table->uuid('uuid')->unique()->after('id');
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('uuid');
        });

        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn('uuid');
        });

        Schema::table('devices', function (Blueprint $table) {
            $table->dropColumn('uuid');
        });

        Schema::table('pins', function (Blueprint $table) {
            $table->dropColumn('uuid');
        });

        Schema::table('pin_logs', function (Blueprint $table) {
            $table->dropColumn('uuid');
        });
    }
}; 