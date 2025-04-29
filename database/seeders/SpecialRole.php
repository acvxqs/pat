<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SpecialRole extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('special_roles')->insert([
            ['code' => 'GC', 'description' => 'Galactic Commander'],
            ['code' => 'MoD', 'description' => 'Minister of Development'],
            ['code' => 'MoC', 'description' => 'Minister of Communication'],
            ['code' => 'MoW', 'description' => 'Minister of War'],
            ['code' => 'P', 'description' => 'Protected'],
            ['code' => 'C', 'description' => 'Closed'],
            ['code' => 'E', 'description' => 'Exiled'],
        ]);
    }
}
