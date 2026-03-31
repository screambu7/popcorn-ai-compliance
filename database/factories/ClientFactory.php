<?php

namespace Database\Factories;

use App\Models\Client;
use Illuminate\Database\Eloquent\Factories\Factory;

class ClientFactory extends Factory
{
    protected $model = Client::class;

    public function definition(): array
    {
        $regimenes = [
            ['601', 'General de Ley Personas Morales'],
            ['612', 'Personas Físicas con Actividades Empresariales y Profesionales'],
            ['625', 'Régimen Simplificado de Confianza (RESICO)'],
            ['606', 'Arrendamiento'],
            ['603', 'Personas Morales con Fines no Lucrativos'],
        ];
        $regimen = $this->faker->randomElement($regimenes);

        $actividadVulnerable = $this->faker->boolean(30);

        return [
            'razon_social' => $this->faker->company(),
            'rfc' => strtoupper($this->faker->bothify('???######???')),
            'email' => $this->faker->unique()->companyEmail(),
            'telefono' => $this->faker->phoneNumber(),
            'regimen_fiscal' => $regimen[0],
            'regimen_fiscal_descripcion' => $regimen[1],
            'actividad_vulnerable' => $actividadVulnerable,
            'actividades_vulnerables' => $actividadVulnerable
                ? $this->faker->randomElements(array_keys(\App\Models\ExpedienteAntilavado::ACTIVIDADES_VULNERABLES), rand(1, 3))
                : null,
            'obligado_antilavado' => $actividadVulnerable,
            'es_pep' => $this->faker->boolean(10),
            'nivel_riesgo' => $this->faker->randomElement(['bajo', 'bajo', 'bajo', 'medio', 'medio', 'alto']),
            'active' => $this->faker->boolean(90),
        ];
    }

    public function vulnerable(): static
    {
        return $this->state(fn() => [
            'actividad_vulnerable' => true,
            'obligado_antilavado' => true,
            'nivel_riesgo' => $this->faker->randomElement(['medio', 'alto']),
        ]);
    }

    public function altoRiesgo(): static
    {
        return $this->state(fn() => [
            'nivel_riesgo' => 'alto',
            'es_pep' => true,
            'actividad_vulnerable' => true,
        ]);
    }
}
