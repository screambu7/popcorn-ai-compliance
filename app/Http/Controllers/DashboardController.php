<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\ExpedienteAntilavado;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function __invoke()
    {
        return Inertia::render('Dashboard', [
            'stats' => [
                'clientes_activos' => Client::active()->count(),
                'clientes_vulnerables' => Client::vulnerable()->count(),
                'clientes_alto_riesgo' => Client::altoRiesgo()->count(),
                'expedientes_pendientes' => ExpedienteAntilavado::pendientes()->count(),
                'expedientes_alto_riesgo' => ExpedienteAntilavado::altoRiesgo()->count(),
                'expedientes_reporte_uif' => ExpedienteAntilavado::requiereReporteUif()->count(),
            ],
            'expedientes_recientes' => ExpedienteAntilavado::with('client')
                ->latest()
                ->take(5)
                ->get(),
            'clientes_alto_riesgo' => Client::altoRiesgo()
                ->active()
                ->latest()
                ->take(5)
                ->get(),
        ]);
    }
}
