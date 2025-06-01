<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('pin_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('type', ['digital_output', 'digital_input', 'analog_input']);
            $table->string('description');
            $table->string('icon');
            $table->json('settings');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('pin_templates');
    }
}; 