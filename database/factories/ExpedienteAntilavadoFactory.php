<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\ExpedienteAntilavado;
use Illuminate\Database\Eloquent\Factories\Factory;

class ExpedienteAntilavadoFactory extends Factory
{
    protected $model = ExpedienteAntilavado::class;

    public function definition(): array
    {
        $monto = $this->faker->randomFloat(2, 5000, 500000);
        $umas = (int) round($monto / ExpedienteAntilavado::UMA_DIARIO_2026);
        $esPep = $this->faker->boolean(15);

        return [
            'client_id' => Client::factory(),
            'folio' => sprintf('KYC-%s-%05d', now()->format('Y'), $this->faker->unique()->numberBetween(1, 99999)),
            'tipo_actividad_vulnerable' => $this->faker->randomElement(array_keys(ExpedienteAntilavado::ACTIVIDADES_VULNERABLES)),
            'descripcion_actividad' => $this->faker->sentence(),
            'monto_operacion' => $monto,
            'moneda' => 'MXN',
            'umas_equivalente' => $umas,
            'supera_umbral_aviso' => $umas >= 645,
            'supera_umbral_reporte' => $umas >= 1500,
            'nombre_beneficiario' => $this->faker->name(),
            'rfc_beneficiario' => strtoupper($this->faker->bothify('???######???')),
            'es_pep' => $esPep,
            'nivel_riesgo' => ($esPep || $umas >= 1500) ? 'alto' : ($umas >= 645 ? 'medio' : 'bajo'),
            'estado' => $this->faker->randomElement(['pendiente', 'pendiente', 'en_revision', 'completo', 'reportado_uif']),
            'fecha_operacion' => $this->faker->dateTimeBetween('-6 months', 'now'),
            'fecha_vencimiento_retencion' => $this->faker->dateTimeBetween('+9 years', '+10 years'),
            'observaciones' => $this->faker->optional()->sentence(),
        ];
    }
}
