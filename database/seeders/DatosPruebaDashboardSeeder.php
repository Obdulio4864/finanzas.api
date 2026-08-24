<?php

namespace Database\Seeders;

use App\Models\Categoria;
use App\Models\Egreso;
use App\Models\Ingreso;
use App\Models\Subcategoria;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatosPruebaDashboardSeeder extends Seeder
{
    private const PASSWORD = 'Finanzas2026!';

    public function run(): void
    {
        $categorias = Categoria::query()
            ->whereNull('user_id')
            ->get()
            ->keyBy('nombre');

        $subcategorias = Subcategoria::query()
            ->whereIn('categoria_id', $categorias->pluck('id'))
            ->get()
            ->keyBy(fn (Subcategoria $subcategoria) => $subcategoria->categoria_id.':'.$subcategoria->nombre);

        $perfiles = [
            [
                'name' => 'Ana López',
                'email' => 'ana.estudiante@example.com',
                'fuente' => 'Apoyo familiar',
                'categoria_ingreso' => 'Otro Ingreso',
                'ingresos' => [1 => '2200.00', 2 => '2200.00', 3 => '2350.00', 4 => '2200.00', 5 => '2400.00', 7 => '2300.00'],
                'egresos' => [
                    ['dia' => 2, 'categoria' => 'Educación', 'subcategoria' => 'Universidad', 'descripcion' => 'Cuota universitaria', 'monto' => '650.00'],
                    ['dia' => 4, 'categoria' => 'Alimentación', 'subcategoria' => 'Supermercado', 'descripcion' => 'Compra de supermercado', 'monto' => '350.00'],
                    ['dia' => 6, 'categoria' => 'Transporte', 'subcategoria' => 'Bus', 'descripcion' => 'Pasajes de bus', 'monto' => '140.00'],
                    ['dia' => 8, 'categoria' => 'Vivienda', 'subcategoria' => 'Internet', 'descripcion' => 'Aporte de internet', 'monto' => '180.00'],
                    ['dia' => 11, 'categoria' => 'Deporte', 'subcategoria' => 'Gimnasio', 'descripcion' => 'Mensualidad de gimnasio', 'monto' => '150.00'],
                    ['dia' => 15, 'categoria' => 'Alimentación', 'subcategoria' => 'Restaurante', 'descripcion' => 'Almuerzo cerca de la universidad', 'monto' => '85.00'],
                    ['dia' => 19, 'categoria' => 'Ocio / Entretenimiento', 'subcategoria' => 'Suscripciones', 'descripcion' => 'Suscripción de música', 'monto' => '55.00'],
                    ['dia' => 23, 'categoria' => 'Salud', 'subcategoria' => 'Medicamentos', 'descripcion' => 'Medicamentos básicos', 'monto' => '65.00'],
                ],
            ],
            [
                'name' => 'Carlos Pérez',
                'email' => 'carlos.estudiante@example.com',
                'fuente' => 'Trabajo de medio tiempo',
                'categoria_ingreso' => 'Empleo',
                'ingresos' => [1 => '2900.00', 2 => '2900.00', 3 => '3050.00', 4 => '2900.00', 5 => '3100.00', 7 => '3000.00'],
                'egresos' => [
                    ['dia' => 1, 'categoria' => 'Vivienda', 'subcategoria' => 'Alquiler', 'descripcion' => 'Aporte de alquiler', 'monto' => '600.00'],
                    ['dia' => 3, 'categoria' => 'Educación', 'subcategoria' => 'Universidad', 'descripcion' => 'Cuota universitaria', 'monto' => '700.00'],
                    ['dia' => 5, 'categoria' => 'Alimentación', 'subcategoria' => 'Supermercado', 'descripcion' => 'Compra de supermercado', 'monto' => '400.00'],
                    ['dia' => 7, 'categoria' => 'Transporte', 'subcategoria' => 'Gasolina', 'descripcion' => 'Gasolina para motocicleta', 'monto' => '220.00'],
                    ['dia' => 10, 'categoria' => 'Vivienda', 'subcategoria' => 'Internet', 'descripcion' => 'Servicio de internet', 'monto' => '200.00'],
                    ['dia' => 14, 'categoria' => 'Alimentación', 'subcategoria' => 'Restaurante', 'descripcion' => 'Almuerzo cerca de la universidad', 'monto' => '95.00'],
                    ['dia' => 18, 'categoria' => 'Ocio / Entretenimiento', 'subcategoria' => 'Cine', 'descripcion' => 'Salida al cine', 'monto' => '80.00'],
                    ['dia' => 22, 'categoria' => 'Salud', 'subcategoria' => 'Consulta', 'descripcion' => 'Consulta médica', 'monto' => '150.00'],
                    ['dia' => 26, 'categoria' => 'Imprevistos', 'subcategoria' => 'Reparaciones', 'descripcion' => 'Reparación de motocicleta', 'monto' => '180.00'],
                ],
            ],
        ];

        foreach ($perfiles as $perfil) {
            $user = User::query()->updateOrCreate(
                ['email' => $perfil['email']],
                [
                    'name' => $perfil['name'],
                    'password' => Hash::make(self::PASSWORD),
                    'email_verified_at' => now(),
                ],
            );

            $categoriaIngreso = $categorias->get($perfil['categoria_ingreso'])
                ?? throw new \RuntimeException('No se encontró la categoría de ingreso requerida.');

            foreach ($perfil['ingresos'] as $mes => $monto) {
                $fecha = sprintf('2026-%02d-01', $mes);

                $this->guardarIngreso($user, [
                    'categoria_id' => $categoriaIngreso->id,
                    'fecha' => $fecha,
                    'fuente' => $perfil['fuente'],
                    'monto' => $monto,
                    'notas' => 'Ingreso de prueba para el dashboard.',
                ]);
            }

            foreach ([1, 2, 3, 4, 5, 7] as $mes) {
                foreach ($perfil['egresos'] as $egreso) {
                    $categoria = $categorias->get($egreso['categoria'])
                        ?? throw new \RuntimeException('No se encontró una categoría de egreso requerida.');
                    $subcategoria = $subcategorias->get($categoria->id.':'.$egreso['subcategoria'])
                        ?? throw new \RuntimeException('No se encontró una subcategoría de egreso requerida.');

                    $this->guardarEgreso($user, [
                        'categoria_id' => $categoria->id,
                        'subcategoria_id' => $subcategoria->id,
                        'fecha' => sprintf('2026-%02d-%02d', $mes, $egreso['dia']),
                        'descripcion' => $egreso['descripcion'],
                        'monto' => $this->aplicarVariacionMensual($egreso['monto'], $mes),
                        'notas' => 'Egreso de prueba para el dashboard.',
                    ]);
                }
            }
        }
    }

    private function guardarIngreso(User $user, array $datos): void
    {
        $ingreso = Ingreso::query()
            ->where('user_id', $user->id)
            ->where('fecha', $datos['fecha'])
            ->where('fuente', $datos['fuente'])
            ->first() ?? new Ingreso();

        $ingreso->forceFill(['user_id' => $user->id, ...$datos])->save();
    }

    private function guardarEgreso(User $user, array $datos): void
    {
        $egreso = Egreso::query()
            ->where('user_id', $user->id)
            ->where('fecha', $datos['fecha'])
            ->where('descripcion', $datos['descripcion'])
            ->first() ?? new Egreso();

        $egreso->forceFill(['user_id' => $user->id, ...$datos])->save();
    }

    private function aplicarVariacionMensual(string $monto, int $mes): string
    {
        [$unidades, $centavos] = explode('.', $monto);
        $totalCentavos = ((int) $unidades * 100) + (int) $centavos + (($mes % 3) - 1) * 500;

        return intdiv($totalCentavos, 100).'.'.str_pad((string) ($totalCentavos % 100), 2, '0', STR_PAD_LEFT);
    }
}
