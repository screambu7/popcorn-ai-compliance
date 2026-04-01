<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\AvisoSppld;
use App\Models\Operacion;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AvisoController extends Controller
{
    public function index(Request $request)
    {
        $query = AvisoSppld::with(['generadoPor', 'aprobadoPor']);

        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        if ($request->filled('tipo')) {
            $query->where('tipo', $request->tipo);
        }

        if ($request->filled('periodo_mes') && $request->filled('periodo_anio')) {
            $query->delPeriodo($request->periodo_mes, $request->periodo_anio);
        }

        return Inertia::render('Avisos/Index', [
            'avisos' => $query->latest()->paginate(15)->withQueryString(),
            'filters' => $request->only(['estado', 'tipo', 'periodo_mes', 'periodo_anio']),
            'estados' => AvisoSppld::ESTADOS,
            'tipos' => AvisoSppld::TIPOS,
        ]);
    }

    public function generate(Request $request)
    {
        $validated = $request->validate([
            'periodo_mes' => 'required|integer|between:1,12',
            'periodo_anio' => 'required|integer|min:2024',
            'tipo' => 'sometimes|string|in:' . implode(',', array_keys(AvisoSppld::TIPOS)),
        ]);

        $mes = $validated['periodo_mes'];
        $anio = $validated['periodo_anio'];
        $tipo = $validated['tipo'] ?? 'normal';

        // Collect operations that exceed notice threshold for the period
        $operaciones = Operacion::delPeriodo($mes, $anio)
            ->superanUmbralAviso()
            ->whereNull('aviso_id')
            ->get();

        // If no qualifying operations, create an "aviso en cero"
        if ($operaciones->isEmpty()) {
            $tipo = 'cero';
        }

        $aviso = AvisoSppld::create([
            'folio' => AvisoSppld::generarFolio(),
            'tipo' => $tipo,
            'periodo_mes' => $mes,
            'periodo_anio' => $anio,
            'fecha_generacion' => now(),
            'total_operaciones' => $operaciones->count(),
            'monto_total' => $operaciones->sum('monto'),
            'estado' => 'generado',
            'generado_por_user_id' => auth()->id(),
        ]);

        // Link operations to this aviso
        if ($operaciones->isNotEmpty()) {
            Operacion::whereIn('id', $operaciones->pluck('id'))
                ->update(['aviso_id' => $aviso->id, 'estado' => 'incluida_aviso']);
        }

        AuditLog::registrar(
            accion: 'aviso_generado',
            entidadTipo: 'AvisoSppld',
            entidadId: $aviso->id,
            datosNuevos: $aviso->toArray(),
            notas: "Periodo: {$mes}/{$anio}. Operaciones vinculadas: {$operaciones->count()}",
        );

        return redirect()->route('avisos.show', $aviso)
            ->with('success', "Aviso {$aviso->folio} generado con {$operaciones->count()} operaciones.");
    }

    public function show(AvisoSppld $aviso)
    {
        return Inertia::render('Avisos/Show', [
            'aviso' => $aviso->load(['operaciones.client', 'generadoPor', 'aprobadoPor']),
            'estados' => AvisoSppld::ESTADOS,
            'tipos' => AvisoSppld::TIPOS,
        ]);
    }

    public function approve(AvisoSppld $aviso)
    {
        $antes = $aviso->toArray();

        $aviso->update([
            'aprobado_por_user_id' => auth()->id(),
        ]);

        AuditLog::registrar(
            accion: 'aviso_aprobado',
            entidadTipo: 'AvisoSppld',
            entidadId: $aviso->id,
            datosAnteriores: $antes,
            datosNuevos: $aviso->fresh()->toArray(),
        );

        return redirect()->route('avisos.show', $aviso)
            ->with('success', 'Aviso aprobado por oficial de cumplimiento.');
    }

    public function updateEstado(Request $request, AvisoSppld $aviso)
    {
        $validated = $request->validate([
            'estado' => 'required|in:' . implode(',', array_keys(AvisoSppld::ESTADOS)),
            'notas' => 'nullable|string',
        ]);

        $antes = $aviso->toArray();

        $updateData = ['estado' => $validated['estado']];

        if ($validated['estado'] === 'enviado' && !$aviso->fecha_envio) {
            $updateData['fecha_envio'] = now();
        }

        if ($validated['estado'] === 'aceptado' && !$aviso->fecha_acuse) {
            $updateData['fecha_acuse'] = now();
        }

        if (isset($validated['notas'])) {
            $updateData['notas'] = $validated['notas'];
        }

        $aviso->update($updateData);

        AuditLog::registrar(
            accion: 'aviso_estado_actualizado',
            entidadTipo: 'AvisoSppld',
            entidadId: $aviso->id,
            datosAnteriores: $antes,
            datosNuevos: $aviso->fresh()->toArray(),
        );

        return redirect()->route('avisos.show', $aviso)
            ->with('success', 'Estado del aviso actualizado.');
    }
}
