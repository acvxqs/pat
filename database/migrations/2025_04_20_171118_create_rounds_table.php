<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations. See: http://www.planetarion.com/games/status/game/
     */
    public function up(): void
    {
        Schema::create('rounds', function (Blueprint $table) {
            $table->smallInteger('number')->unsigned()->primary(); // Unique round ID
            $table->string('name'); // Name of the round
            $table->smallInteger('current_tick')->unsigned(); // Current tick of the round
            $table->smallInteger('tick_speed')->unsigned(); // Speed of the tick in seconds
            $table->boolean('ticking'); // Indicates if the round is currently ticking
            $table->timestampTz('last_tick_happened_at', 0); // TimestampTz of the last tick
            $table->smallinteger('max_membercount')->unsigned(); // Maximum number of members per alliance
            $table->smallInteger('members_counting_towards_alliance_score')->unsigned(); // Number of members counting towards alliance score
            $table->smallInteger('xp_per_tick_defending_universe')->unsigned(); // XP gained per tick for defending the universe
            $table->smallInteger('xp_per_tick_defending_galaxy')->unsigned(); // XP gained per tick for defending the galaxy
            $table->smallInteger('xp_landing_defense')->unsigned(); // XP gained for landing defense
            $table->smallInteger('max_cap')->unsigned(); // max capture asteroids (percentage)
            $table->smallInteger('max_structures_destroyed')->unsigned(); // max structures destroyed (percentage)
            $table->smallInteger('salvage_from_attacking_ships')->unsigned(); // salvage from attacking ships (percentage)
            $table->smallInteger('salvage_from_defending_ships')->unsigned(); // salvage from defending ships (percentage)
            $table->smallInteger('asteroid_armor')->unsigned(); // armor of asteroids
            $table->smallInteger('construction_armor')->unsigned(); // armor of construction
            $table->smallInteger('damage_done_on_primary_target')->unsigned(); // damage done on primary target (percentage)
            $table->smallInteger('damage_done_on_secondary_target')->unsigned(); // damage done on secondary target (percentage)
            $table->smallInteger('damage_done_on_tertiary_target')->unsigned(); // damage done on tertiary target (percentage)
            $table->boolean('pods_die_when_capping'); // indicates if pods die when capping
            $table->boolean('structure_killers_die'); // indicates if structure killers die
            $table->smallInteger('stealship_steal_die_ratio')->unsigned(); // stealship steal die ratio (percentage)
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rounds');
    }
};
