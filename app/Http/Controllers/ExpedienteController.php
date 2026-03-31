<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Client;
use App\Models\ExpedienteAntilavado;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ExpedienteController extends Controller
{
    public function index(Request $request)
    {
        $query = ExpedienteAntilavado::with('client');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('folio', 'like', "%{$search}%")
                  ->orWhere('nombre_beneficiario', 'like', "%{$search}%")
                  ->orWhere('rfc_beneficiario', 'like', "%{$search}%")
                  ->orWhereHas('client', fn($q) => $q->where('razon_social', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        if ($request->filled('nivel_riesgo')) {
            $query->where('nivel_riesgo', $request->nivel_riesgo);
        }

        if ($request->filled('client_id')) {
            $query->where('client_id', $request->client_id);
        }

        return Inertia::render('Expedientes/Index', [
            'expedientes' => $query->latest()->paginate(15)->withQueryString(),
            'filters' => $request->only(['search', 'estado', 'nivel_riesgo', 'client_id']),
            'estados' => ExpedienteAntilavado::ESTADOS,
        ]);
    }

    public function create(Request $request)
    {
        return Inertia::render('Expedientes/Create', [
            'clients' => Client::active()->orderBy('razon_social')->get(['id', 'razon_social', 'rfc']),
            'actividades_vulnerables' => ExpedienteAntilavado::ACTIVIDADES_VULNERABLES,
            'uma_diario' => ExpedienteAntilavado::UMA_DIARIO_2026,
            'preselected_client_id' => $request->client_id,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'tipo_actividad_vulnerable' => 'required|string|in:' . implode(',', array_keys(ExpedienteAntilavado::ACTIVIDADES_VULNERABLES)),
            'descripcion_actividad' => 'nullable|string',
            'monto_operacion' => 'required|numeric|min:0',
            'moneda' => 'required|string|size:3',
            'nombre_beneficiario' => 'nullable|string|max:255',
            'rfc_beneficiario' => 'nullable|string|max:13',
            'es_pep' => 'boolean',
            'fecha_operacion' => 'required|date',
            'observaciones' => 'nullable|string',
        ]);

        $expediente = new ExpedienteAntilavado($validated);
        $expediente->folio = ExpedienteAntilavado::generarFolio();
        $expediente->calcularUmbrales();
        $expediente->calcularRiesgo();
        $expediente->calcularRetencion();
        $expediente->save();

        AuditLog::registrar(
            accion: 'expediente_creado',
            clientId: $expediente->client_id,
            entidadTipo: 'ExpedienteAntilavado',
            entidadId: $expediente->id,
            datosNuevos: $expediente->toArray(),
        );

        return redirect()->route('expedientes.show', $expediente)
            ->with('success', "Expediente {$expediente->folio} creado correctamente.");
    }

    public function show(ExpedienteAntilavado $expediente)
    {
        return Inertia::render('Expedientes/Show', [
            'expediente' => $expediente->load('client'),
            'uma_diario' => ExpedienteAntilavado::UMA_DIARIO_2026,
            'estados' => ExpedienteAntilavado::ESTADOS,
        ]);
    }

    public function update(Request $request, ExpedienteAntilavado $expediente)
    {
        $validated = $request->validate([
            'estado' => 'sometimes|in:' . implode(',', array_keys(ExpedienteAntilavado::ESTADOS)),
            'observaciones' => 'nullable|string',
            'fecha_reporte_uif' => 'nullable|date',
        ]);

        $antes = $expediente->toArray();

        if (isset($validated['estado']) && $validated['estado'] === 'reportado_uif' && !$expediente->fecha_reporte_uif) {
            $validated['fecha_reporte_uif'] = now();
        }

        $expediente->update($validated);

        AuditLog::registrar(
            accion: 'expediente_actualizado',
            clientId: $expediente->client_id,
            entidadTipo: 'ExpedienteAntilavado',
            entidadId: $expediente->id,
            datosAnteriores: $antes,
            datosNuevos: $expediente->fresh()->toArray(),
        );

        return redirect()->route('expedientes.show', $expediente)
            ->with('success', 'Expediente actualizado.');
    }
}
