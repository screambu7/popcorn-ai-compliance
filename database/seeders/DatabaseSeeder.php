<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\ExpedienteAntilavado;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        // Admin user
        User::factory()->create([
            'name' => 'Admin Popcorn',
            'email' => 'admin@popcorn.mx',
        ]);

        User::factory()->create([
            'name' => 'Ted Cherem',
            'email' => 'ted@popcorn.mx',
        ]);

        // Clientes con datos realistas mexicanos
        $clientesReales = [
            ['razon_social' => 'Grupo Inmobiliario del Norte SA de CV', 'rfc' => 'GIN190415H70', 'regimen_fiscal' => '601', 'actividad_vulnerable' => true, 'actividades_vulnerables' => ['VII'], 'nivel_riesgo' => 'medio'],
            ['razon_social' => 'Casa de Cambio Express SA de CV', 'rfc' => 'CCE200310KL5', 'regimen_fiscal' => '601', 'actividad_vulnerable' => true, 'actividades_vulnerables' => ['X'], 'nivel_riesgo' => 'alto', 'es_pep' => true],
            ['razon_social' => 'Joyería Hernández y Asociados', 'rfc' => 'JHA180923QR4', 'regimen_fiscal' => '612', 'actividad_vulnerable' => true, 'actividades_vulnerables' => ['VIII'], 'nivel_riesgo' => 'medio'],
            ['razon_social' => 'Notaría Pública No. 47 del Estado de México', 'rfc' => 'NPC150812AB3', 'regimen_fiscal' => '612', 'actividad_vulnerable' => true, 'actividades_vulnerables' => ['XIII'], 'nivel_riesgo' => 'bajo'],
            ['razon_social' => 'Automotriz Premium del Valle SA de CV', 'rfc' => 'APV210605CD8', 'regimen_fiscal' => '601', 'actividad_vulnerable' => true, 'actividades_vulnerables' => ['IV', 'IX'], 'nivel_riesgo' => 'alto'],
            ['razon_social' => 'Despacho Jurídico Mendoza & Partners SC', 'rfc' => 'DJM170301EF1', 'regimen_fiscal' => '601', 'actividad_vulnerable' => true, 'actividades_vulnerables' => ['XIV'], 'nivel_riesgo' => 'bajo'],
            ['razon_social' => 'Transportadora de Valores Segura SA de CV', 'rfc' => 'TVS190720GH6', 'regimen_fiscal' => '601', 'actividad_vulnerable' => true, 'actividades_vulnerables' => ['III', 'V'], 'nivel_riesgo' => 'alto'],
        ];

        foreach ($clientesReales as $data) {
            Client::create(array_merge([
                'email' => fake()->companyEmail(),
                'telefono' => fake()->phoneNumber(),
                'regimen_fiscal_descripcion' => $data['regimen_fiscal'] === '601' ? 'General de Ley Personas Morales' : 'Personas Físicas con Actividades Empresariales y Profesionales',
                'obligado_antilavado' => $data['actividad_vulnerable'],
                'es_pep' => false,
                'active' => true,
            ], $data));
        }

        // Clientes genéricos sin actividad vulnerable
        Client::factory(15)->create();

        // Clientes vulnerables adicionales
        Client::factory(5)->vulnerable()->create();

        // Expedientes para los clientes reales
        $clients = Client::where('actividad_vulnerable', true)->get();
        foreach ($clients as $client) {
            ExpedienteAntilavado::factory(rand(1, 4))->create([
                'client_id' => $client->id,
                'tipo_actividad_vulnerable' => $client->actividades_vulnerables[0] ?? 'VII',
            ]);
        }
    }
}
