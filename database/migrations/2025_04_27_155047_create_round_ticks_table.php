<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('round_ticks', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('round_number');
            $table->foreign('round_number')->references('number')->on('rounds');
            $table->unsignedMediumInteger('number');
            $table->unique(['number', 'round_number']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ticks');
    }
};
