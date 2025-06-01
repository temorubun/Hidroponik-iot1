<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pin_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pin_id')->constrained()->onDelete('cascade');
            $table->float('value')->nullable();
            $table->float('raw_value')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pin_logs');
    }
}; 