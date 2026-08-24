<?php

namespace Database\Factories;

use App\Models\Categoria;
use App\Models\Egreso;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Egreso>
 */
class EgresoFactory extends Factory
{
    protected $model = Egreso::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'categoria_id' => Categoria::factory()->state(['tipo' => 'egreso']),
            'subcategoria_id' => null,
            'fecha' => fake()->dateTimeBetween('-1 year', 'now')->format('Y-m-d'),
            'descripcion' => fake()->sentence(3),
            'monto' => $this->montoDecimal(20, 1500),
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
