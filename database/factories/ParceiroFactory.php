<?php

namespace Database\Factories;

use App\Models\Parceiro;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Model>
 */
class ParceiroFactory extends Factory
{

    protected $model = Parceiro::class;
    
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nome' => $this->faker->name,
            'tipo_vinculo' => $this->faker->randomElement(['Cliente', 'Fornecedor']),
            'tipo_documento' => 'CNPJ',
            'nro_documento' => $this->faker->unique()->numerify('###########'),
            'ativo' => $this->faker->boolean,
            'created_by' => 1,
            'updated_by' => 1,
        ];
    }
}
