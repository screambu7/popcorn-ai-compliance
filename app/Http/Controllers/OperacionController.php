<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Client;
use App\Models\Operacion;
use Illuminate\Http\Request;
use Inertia\Inertia;

class OperacionController extends Controller
{
    public function index(Request $request)
    {
        $query = Operacion::with('client');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('folio', 'like', "%{$search}%")
                  ->orWhere('contraparte_nombre', 'like', "%{$search}%")
                  ->orWhere('contraparte_rfc', 'like', "%{$search}%")
                  ->orWhereHas('client', fn($q) => $q->where('razon_social', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('client_id')) {
            $query->where('client_id', $request->client_id);
        }

        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        if ($request->filled('nivel_riesgo')) {
            $query->whereHas('client', fn($q) => $q->where('nivel_riesgo', $request->nivel_riesgo));
        }

        if ($request->filled('fecha_desde')) {
            $query->where('fecha_operacion', '>=', $request->fecha_desde);
        }

        if ($request->filled('fecha_hasta')) {
            $query->where('fecha_operacion', '<=', $request->fecha_hasta);
        }

        return Inertia::render('Operaciones/Index', [
            'operaciones' => $query->latest('fecha_operacion')->paginate(15)->withQueryString(),
            'filters' => $request->only(['search', 'client_id', 'estado', 'nivel_riesgo', 'fecha_desde', 'fecha_hasta']),
            'actividades_vulnerables' => Operacion::ACTIVIDADES_VULNERABLES,
        ]);
    }

    public function create(Request $request)
    {
        return Inertia::render('Operaciones/Create', [
            'clients' => Client::active()->orderBy('razon_social')->get(['id', 'razon_social', 'rfc']),
            'actividades_vulnerables' => Operacion::ACTIVIDADES_VULNERABLES,
            'uma_diario' => Operacion::UMA_DIARIO_2026,
            'preselected_client_id' => $request->client_id,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'tipo_actividad_vulnerable' => 'required|string|in:' . implode(',', array_keys(Operacion::ACTIVIDADES_VULNERABLES)),
            'descripcion' => 'nullable|string',
            'monto' => 'required|numeric|min:0',
            'moneda' => 'required|string|size:3',
            'forma_pago' => 'required|string|max:50',
            'monto_efectivo' => 'nullable|numeric|min:0',
            'contraparte_nombre' => 'nullable|string|max:255',
            'contraparte_rfc' => 'nullable|string|max:13',
            'fecha_operacion' => 'required|date',
            'notas' => 'nullable|string',
        ]);

        $operacion = new Operacion($validated);
        $operacion->user_id = auth()->id();
        $operacion->folio = Operacion::generarFolio();
        $operacion->estado = 'registrada';
        $operacion->calcularUmbrales();
        $operacion->save();

        AuditLog::registrar(
            accion: 'operacion_creada',
            clientId: $operacion->client_id,
            entidadTipo: 'Operacion',
            entidadId: $operacion->id,
            datosNuevos: $operacion->toArray(),
        );

        return redirect()->route('operaciones.show', $operacion)
            ->with('success', "Operación {$operacion->folio} creada correctamente.");
    }

    public function show(Operacion $operacion)
    {
        return Inertia::render('Operaciones/Show', [
            'operacion' => $operacion->load(['client', 'avisoSppld', 'user']),
            'uma_diario' => Operacion::UMA_DIARIO_2026,
            'actividades_vulnerables' => Operacion::ACTIVIDADES_VULNERABLES,
        ]);
    }
}
