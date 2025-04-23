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
        Schema::create('round_race_data', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('round_number');
            $table->foreignId('race_id')->constrained()->onDelete('cascade');
            $table->text('description')->nullable();
            $table->unsignedSmallInteger('max_stealth');
            $table->unsignedSmallInteger('stealth_growth_per_tick');
            $table->unsignedSmallInteger('base_construction_units');
            $table->unsignedSmallInteger('base_research_points');
            // Below all values that represent percentages.
            // For example, a value of 50 means 50%.
            $table->unsignedSmallInteger('salvage_bonus');
            $table->unsignedSmallInteger('production_time_bonus');
            $table->unsignedSmallInteger('universe_trade_tax');

            $table->timestamps();
        
            $table->unique(['round_number', 'race_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('round_race_data');
    }
};
