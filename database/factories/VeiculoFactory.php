<?php

namespace Database\Factories;

use App\Models\Parceiro;
use App\Models\Veiculo;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Model>
 */
class VeiculoFactory extends Factory
{
    
    protected $model = Veiculo::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'parceiro_id' => Parceiro::factory(),
            'marca' => $this->faker->company,
            'modelo' => $this->faker->word,
            'placa' => strtoupper($this->faker->unique()->bothify('???####')),
            'created_by' => 1,
            'updated_by' => 1,
        ];
    }
}
