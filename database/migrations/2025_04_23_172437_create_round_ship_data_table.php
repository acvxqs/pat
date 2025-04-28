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
        Schema::create('round_ship_data', function (Blueprint $table) {
            $table->id();

            $table->unsignedSmallInteger('round_number');
            $table->foreign('round_number')->references('number')->on('rounds');
            $table->foreignId('ship_id')->constrained();
            $table->foreignId('races_id')->constrained();
            $table->foreignId('unit_class_id')->constrained();
            $table->foreignId('eta_id')->constrained();

            $table->foreignId('target1_id')->constrained('unit_classes');
            $table->foreignId('target2_id')->nullable()->constrained('unit_classes');
            $table->foreignId('target3_id')->nullable()->constrained('unit_classes');

            $table->foreignId('weapon_type_id')->constrained();

            $table->boolean('cloaked');
            $table->unsignedSmallInteger('initiative');

            $table->unsignedInteger('guns');
            $table->unsignedInteger('armor');
            $table->unsignedInteger('damage');
            $table->unsignedInteger('empres');

            $table->unsignedInteger('cost_m');
            $table->unsignedInteger('cost_c');
            $table->unsignedInteger('cost_e');

            $table->unsignedInteger('armorcost');
            $table->unsignedInteger('damagecost');

            $table->timestamps();

            $table->unique(['round_number', 'ship_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('round_ship_data');
    }
};
