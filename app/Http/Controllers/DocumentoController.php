<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Client;
use App\Models\Documento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class DocumentoController extends Controller
{
    public function index(Request $request)
    {
        $query = Documento::with('client');

        if ($request->filled('client_id')) {
            $query->where('client_id', $request->client_id);
        }

        if ($request->filled('tipo')) {
            $query->where('tipo', $request->tipo);
        }

        if ($request->boolean('vencidos')) {
            $query->vencidos();
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nombre_archivo', 'like', "%{$search}%")
                  ->orWhereHas('client', fn($q) => $q->where('razon_social', 'like', "%{$search}%"));
            });
        }

        return Inertia::render('Documentos/Index', [
            'documentos' => $query->latest()->paginate(15)->withQueryString(),
            'filters' => $request->only(['client_id', 'tipo', 'vencidos', 'search']),
            'tipos' => Documento::TIPOS,
            'clients' => Client::active()->orderBy('razon_social')->get(['id', 'razon_social', 'rfc']),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'tipo' => 'required|string|in:' . implode(',', array_keys(Documento::TIPOS)),
            'archivo' => 'required|file|max:10240|mimes:pdf,jpg,jpeg,png,doc,docx',
            'fecha_emision' => 'nullable|date',
            'fecha_vencimiento' => 'nullable|date|after_or_equal:fecha_emision',
            'notas' => 'nullable|string',
        ]);

        $file = $request->file('archivo');
        $path = $file->store('documentos', 'local');

        $documento = Documento::create([
            'client_id' => $validated['client_id'],
            'tipo' => $validated['tipo'],
            'nombre_archivo' => $file->getClientOriginalName(),
            'ruta_archivo' => $path,
            'mime_type' => $file->getMimeType(),
            'tamano_bytes' => $file->getSize(),
            'fecha_emision' => $validated['fecha_emision'] ?? null,
            'fecha_vencimiento' => $validated['fecha_vencimiento'] ?? null,
            'verificado' => false,
            'notas' => $validated['notas'] ?? null,
        ]);

        AuditLog::registrar(
            accion: 'documento_subido',
            clientId: $documento->client_id,
            entidadTipo: 'Documento',
            entidadId: $documento->id,
            datosNuevos: $documento->toArray(),
        );

        return redirect()->route('documentos.index', ['client_id' => $documento->client_id])
            ->with('success', 'Documento subido correctamente.');
    }

    public function verify(Request $request, Documento $documento)
    {
        $antes = $documento->toArray();

        $documento->update([
            'verificado' => true,
            'verificado_por_user_id' => auth()->id(),
            'verificado_at' => now(),
        ]);

        AuditLog::registrar(
            accion: 'documento_verificado',
            clientId: $documento->client_id,
            entidadTipo: 'Documento',
            entidadId: $documento->id,
            datosAnteriores: $antes,
            datosNuevos: $documento->fresh()->toArray(),
        );

        return redirect()->back()
            ->with('success', 'Documento verificado correctamente.');
    }

    public function download(Documento $documento)
    {
        if (!Storage::disk('local')->exists($documento->ruta_archivo)) {
            abort(404, 'Archivo no encontrado.');
        }

        return Storage::disk('local')->download(
            $documento->ruta_archivo,
            $documento->nombre_archivo
        );
    }
}
