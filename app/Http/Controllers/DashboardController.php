<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\AvisoSppld;
use App\Models\Client;
use App\Models\Documento;
use App\Models\ExpedienteAntilavado;
use App\Models\Operacion;
use App\Models\ScreeningResult;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function __invoke()
    {
        $totalClientes = Client::active()->count();
        $clientesVerde = Client::active()->complianceStatus('verde')->count();

        return Inertia::render('Dashboard', [
            'stats' => [
                'clientes_activos' => $totalClientes,
                'clientes_vulnerables' => Client::vulnerable()->count(),
                'clientes_alto_riesgo' => Client::altoRiesgo()->count(),
                'expedientes_pendientes' => ExpedienteAntilavado::pendientes()->count(),
                'expedientes_alto_riesgo' => ExpedienteAntilavado::altoRiesgo()->count(),
                'expedientes_reporte_uif' => ExpedienteAntilavado::requiereReporteUif()->count(),
                'operaciones_mes_actual' => Operacion::delPeriodo(now()->month, now()->year)->count(),
                'avisos_pendientes' => AvisoSppld::pendientes()->count(),
                'screening_pendientes' => ScreeningResult::pendientesRevision()->count(),
                'documentos_vencidos' => Documento::vencidos()->count(),
                'compliance_score' => $totalClientes > 0
                    ? round(($clientesVerde / $totalClientes) * 100, 1)
                    : 100,
            ],
            'operaciones_por_mes' => $this->operacionesPorMes(),
            'semaforo_distribucion' => [
                'verde' => $clientesVerde,
                'amarillo' => Client::active()->complianceStatus('amarillo')->count(),
                'rojo' => Client::active()->complianceStatus('rojo')->count(),
            ],
            'actividad_reciente' => AuditLog::with(['client', 'user'])
                ->latest('created_at')
                ->take(10)
                ->get(),
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

    /**
     * Operaciones agrupadas por mes (últimos 6 meses).
     */
    private function operacionesPorMes(): array
    {
        $results = [];

        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $mes = $date->month;
            $anio = $date->year;

            $data = Operacion::delPeriodo($mes, $anio)
                ->selectRaw('COUNT(*) as count, COALESCE(SUM(monto), 0) as monto_total')
                ->first();

            $results[] = [
                'mes' => $date->translatedFormat('M Y'),
                'count' => (int) $data->count,
                'monto_total' => (float) $data->monto_total,
            ];
        }

        return $results;
    }
}
