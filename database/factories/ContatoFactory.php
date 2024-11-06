<?php

namespace Database\Factories;

use App\Models\Contato;
use App\Models\Parceiro;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Model>
 */
class ContatoFactory extends Factory
{

    protected $model = Contato::class;
    
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'parceiro_id' => Parceiro::factory(),
            'email' => $this->faker->unique()->safeEmail,
            'telefone_fixo' => $this->faker->optional()->numerify('(##) ####-####'),
            'telefone_cel' => $this->faker->optional()->numerify('(##) 9####-####'),
            'envio_ordem' => $this->faker->boolean,
            'created_by' => 1,
            'updated_by' => 1,
        ];
    }
}
