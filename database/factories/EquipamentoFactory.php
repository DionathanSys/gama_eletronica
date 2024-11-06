<?php

namespace Database\Factories;

use App\Models\Equipamento;
use App\Models\Parceiro;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Model>
 */
class EquipamentoFactory extends Factory
{

    protected $model = Equipamento::class;
    
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'parceiro_id' => Parceiro::factory(),
            'descricao' => $this->faker->sentence(3),
            'nro_serie' => $this->faker->unique()->numerify('SN########'),
            'modelo' => $this->faker->word,
            'marca' => $this->faker->company,
            'created_by' => 1,
            'updated_by' => 1,
        ];
    }
}
