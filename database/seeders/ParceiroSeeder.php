<?php

namespace Database\Seeders;

use App\Models\Parceiro;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ParceiroSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Parceiro::factory()->count(149)->create();
    }
}
