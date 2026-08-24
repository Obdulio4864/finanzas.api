<?php

namespace Database\Factories;

use App\Models\Categoria;
use App\Models\Ingreso;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Ingreso>
 */
class IngresoFactory extends Factory
{
    protected $model = Ingreso::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'categoria_id' => Categoria::factory()->state(['tipo' => 'ingreso']),
            'fecha' => fake()->dateTimeBetween('-1 year', 'now')->format('Y-m-d'),
            'fuente' => fake()->randomElement(['Trabajo de medio tiempo', 'Apoyo familiar', 'Freelance']),
            'monto' => $this->montoDecimal(500, 4000),
            'notas' => fake()->optional()->sentence(),
        ];
    }

    private function montoDecimal(int $minimo, int $maximo): string
    {
        return fake()->numberBetween($minimo, $maximo).'.'.str_pad(
            (string) fake()->numberBetween(0, 99),
            2,
            '0',
            STR_PAD_LEFT,
        );
    }
}
