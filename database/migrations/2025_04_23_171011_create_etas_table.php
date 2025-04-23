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
        Schema::create('etas', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('value')->unique();
            $table->string('label')->nullable(); // e.g. "Fast, Medium, Slow, etc."
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('etas');
    }
};
