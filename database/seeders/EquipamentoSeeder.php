<?php

namespace Database\Seeders;

use App\Models\Equipamento;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class EquipamentoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Equipamento::factory()->count(480)->create();
    }
}
