<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Client;
use App\Models\ExpedienteAntilavado;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WebhookController extends Controller
{
    /**
     * n8n → Laravel: Crear expediente KYC desde workflow antilavado.
     */
    public function kyc(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'rfc' => 'required|string|max:13',
            'tipo_actividad_vulnerable' => 'required|string',
            'monto_operacion' => 'required|numeric|min:0',
            'moneda' => 'sometimes|string|size:3',
            'nombre_beneficiario' => 'nullable|string',
            'rfc_beneficiario' => 'nullable|string|max:13',
            'es_pep' => 'sometimes|boolean',
            'descripcion_actividad' => 'nullable|string',
            'fecha_operacion' => 'sometimes|date',
        ]);

        $client = Client::where('rfc', $validated['rfc'])->first();

        if (!$client) {
            return response()->json([
                'error' => 'Cliente no encontrado',
                'rfc' => $validated['rfc'],
            ], 404);
        }

        $expediente = new ExpedienteAntilavado([
            'client_id' => $client->id,
            'tipo_actividad_vulnerable' => $validated['tipo_actividad_vulnerable'],
            'descripcion_actividad' => $validated['descripcion_actividad'] ?? null,
            'monto_operacion' => $validated['monto_operacion'],
            'moneda' => $validated['moneda'] ?? 'MXN',
            'nombre_beneficiario' => $validated['nombre_beneficiario'] ?? $client->razon_social,
            'rfc_beneficiario' => $validated['rfc_beneficiario'] ?? $client->rfc,
            'es_pep' => $validated['es_pep'] ?? $client->es_pep,
            'fecha_operacion' => $validated['fecha_operacion'] ?? now(),
        ]);

        $expediente->folio = ExpedienteAntilavado::generarFolio();
        $expediente->calcularUmbrales();
        $expediente->calcularRiesgo();
        $expediente->calcularRetencion();
        $expediente->save();

        AuditLog::registrar(
            accion: 'expediente_creado',
            clientId: $client->id,
            entidadTipo: 'ExpedienteAntilavado',
            entidadId: $expediente->id,
            datosNuevos: $expediente->toArray(),
            canal: 'n8n',
        );

        return response()->json([
            'success' => true,
            'folio' => $expediente->folio,
            'nivel_riesgo' => $expediente->nivel_riesgo,
            'umas_equivalente' => $expediente->umas_equivalente,
            'supera_umbral_aviso' => $expediente->supera_umbral_aviso,
            'supera_umbral_reporte' => $expediente->supera_umbral_reporte,
            'requiere_reporte_uif' => $expediente->supera_umbral_reporte || $expediente->es_pep,
        ], 201);
    }
}
