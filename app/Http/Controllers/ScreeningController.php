<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\ScreeningResult;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ScreeningController extends Controller
{
    public function index(Request $request)
    {
        $query = ScreeningResult::with('client');

        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        if ($request->filled('lista_tipo')) {
            $query->where('lista_tipo', $request->lista_tipo);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('match_nombre', 'like', "%{$search}%")
                  ->orWhere('match_rfc', 'like', "%{$search}%")
                  ->orWhereHas('client', fn($q) => $q->where('razon_social', 'like', "%{$search}%"));
            });
        }

        return Inertia::render('Screening/Index', [
            'results' => $query->latest()->paginate(15)->withQueryString(),
            'filters' => $request->only(['estado', 'lista_tipo', 'search']),
            'estados' => ScreeningResult::ESTADOS,
            'listas' => ScreeningResult::LISTAS,
        ]);
    }

    public function review(Request $request, ScreeningResult $result)
    {
        $validated = $request->validate([
            'estado' => 'required|in:confirmado,descartado,falso_positivo',
            'notas' => 'nullable|string',
        ]);

        $antes = $result->toArray();

        $result->update([
            'estado' => $validated['estado'],
            'revisado_por_user_id' => auth()->id(),
            'revisado_at' => now(),
            'notas' => $validated['notas'] ?? $result->notas,
        ]);

        // Recalculate client compliance semaphore after review
        $result->client->calcularSemaforo();

        AuditLog::registrar(
            accion: 'screening_revisado',
            clientId: $result->client_id,
            entidadTipo: 'ScreeningResult',
            entidadId: $result->id,
            datosAnteriores: $antes,
            datosNuevos: $result->fresh()->toArray(),
        );

        return redirect()->route('screening.index')
            ->with('success', 'Resultado de screening actualizado.');
    }
}
