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
        Schema::create('round_government_data', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('round_number');
            $table->foreignId('government_id')->constrained()->onDelete('cascade');
        
            $table->text('description')->nullable();
            // Below all unsignedSmallIntegers. They represent percentage values.
            // For example, a value of 50 means 50%.
            $table->unsignedSmallInteger('mining_output');
            $table->unsignedSmallInteger('research');
            $table->unsignedSmallInteger('construction');
            $table->unsignedSmallInteger('alert');
            $table->unsignedSmallInteger('stealth');
            $table->unsignedSmallInteger('production_time');
            $table->unsignedSmallInteger('production_cost');
            
            $table->timestamps();
        
            $table->unique(['round_number', 'government_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('round_government_data');
    }
};
