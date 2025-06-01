<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pins', function (Blueprint $table) {
            $table->id();
            $table->foreignId('device_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->integer('pin_number');
            $table->string('type');
            $table->json('settings')->nullable();
            $table->boolean('is_active')->default(true);
            $table->float('value')->nullable();
            $table->timestamp('last_update')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pins');
    }
}; 