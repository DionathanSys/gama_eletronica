<?php

namespace Database\Factories;

use App\Models\Endereco;
use App\Models\Parceiro;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Model>
 */
class EnderecoFactory extends Factory
{
    
    protected $model = Endereco::class;
    
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'parceiro_id' => Parceiro::factory(),
            'rua' => $this->faker->streetName,
            'numero' => $this->faker->buildingNumber,
            'complemento' => $this->faker->randomElement(['d', 'e']),
            'bairro' => $this->faker->citySuffix,
            'codigo_municipio' => $this->faker->optional()->numerify('##########'),
            'cidade' => $this->faker->city,
            'estado' => 'SC',
            'cep' => $this->faker->postcode,
            'pais' => 'Brasil',
            'created_by' => 1,
            'updated_by' => 1,
        ];
    }
}
