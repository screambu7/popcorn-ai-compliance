<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Client;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ClientController extends Controller
{
    public function index(Request $request)
    {
        $query = Client::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('razon_social', 'like', "%{$search}%")
                  ->orWhere('rfc', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('nivel_riesgo')) {
            $query->where('nivel_riesgo', $request->nivel_riesgo);
        }

        if ($request->boolean('solo_vulnerables')) {
            $query->vulnerable();
        }

        if ($request->has('active')) {
            $query->where('active', $request->boolean('active'));
        }

        return Inertia::render('Clients/Index', [
            'clients' => $query->latest()->paginate(15)->withQueryString(),
            'filters' => $request->only(['search', 'nivel_riesgo', 'solo_vulnerables', 'active']),
        ]);
    }

    public function create()
    {
        return Inertia::render('Clients/Create', [
            'actividades_vulnerables' => \App\Models\ExpedienteAntilavado::ACTIVIDADES_VULNERABLES,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'razon_social' => 'required|string|max:255',
            'rfc' => 'required|string|size:13|unique:clients,rfc',
            'email' => 'required|email|max:255',
            'telefono' => 'nullable|string|max:20',
            'regimen_fiscal' => 'required|string|max:10',
            'regimen_fiscal_descripcion' => 'nullable|string|max:255',
            'actividad_vulnerable' => 'boolean',
            'actividades_vulnerables' => 'nullable|array',
            'obligado_antilavado' => 'boolean',
            'es_pep' => 'boolean',
            'nivel_riesgo' => 'required|in:bajo,medio,alto',
        ]);

        $client = Client::create($validated);

        AuditLog::registrar(
            accion: 'cliente_creado',
            clientId: $client->id,
            entidadTipo: 'Client',
            entidadId: $client->id,
            datosNuevos: $client->toArray(),
        );

        return redirect()->route('clients.show', $client)
            ->with('success', 'Cliente creado correctamente.');
    }

    public function show(Client $client)
    {
        return Inertia::render('Clients/Show', [
            'client' => $client->load(['expedientes' => fn($q) => $q->latest()->take(10)]),
            'audit_logs' => $client->auditLogs()->latest()->take(20)->get(),
        ]);
    }

    public function edit(Client $client)
    {
        return Inertia::render('Clients/Edit', [
            'client' => $client,
            'actividades_vulnerables' => \App\Models\ExpedienteAntilavado::ACTIVIDADES_VULNERABLES,
        ]);
    }

    public function update(Request $request, Client $client)
    {
        $validated = $request->validate([
            'razon_social' => 'required|string|max:255',
            'rfc' => 'required|string|size:13|unique:clients,rfc,' . $client->id,
            'email' => 'required|email|max:255',
            'telefono' => 'nullable|string|max:20',
            'regimen_fiscal' => 'required|string|max:10',
            'regimen_fiscal_descripcion' => 'nullable|string|max:255',
            'actividad_vulnerable' => 'boolean',
            'actividades_vulnerables' => 'nullable|array',
            'obligado_antilavado' => 'boolean',
            'es_pep' => 'boolean',
            'nivel_riesgo' => 'required|in:bajo,medio,alto',
            'active' => 'boolean',
        ]);

        $antes = $client->toArray();
        $client->update($validated);

        AuditLog::registrar(
            accion: 'cliente_actualizado',
            clientId: $client->id,
            entidadTipo: 'Client',
            entidadId: $client->id,
            datosAnteriores: $antes,
            datosNuevos: $client->fresh()->toArray(),
        );

        return redirect()->route('clients.show', $client)
            ->with('success', 'Cliente actualizado correctamente.');
    }

    public function destroy(Client $client)
    {
        AuditLog::registrar(
            accion: 'cliente_eliminado',
            clientId: $client->id,
            entidadTipo: 'Client',
            entidadId: $client->id,
            datosAnteriores: $client->toArray(),
        );

        $client->delete();

        return redirect()->route('clients.index')
            ->with('success', 'Cliente eliminado correctamente.');
    }
}
