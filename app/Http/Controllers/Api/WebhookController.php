<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Client;
use App\Models\ExpedienteAntilavado;
use App\Models\Operacion;
use App\Models\ScreeningResult;
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

    /**
     * n8n → Laravel: Crear operación desde workflow.
     */
    public function operacion(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'rfc' => 'required|string|max:13',
            'tipo_actividad_vulnerable' => 'required|string|in:' . implode(',', array_keys(Operacion::ACTIVIDADES_VULNERABLES)),
            'descripcion' => 'nullable|string',
            'monto' => 'required|numeric|min:0',
            'moneda' => 'sometimes|string|size:3',
            'forma_pago' => 'sometimes|string|max:50',
            'monto_efectivo' => 'nullable|numeric|min:0',
            'contraparte_nombre' => 'nullable|string|max:255',
            'contraparte_rfc' => 'nullable|string|max:13',
            'fecha_operacion' => 'sometimes|date',
            'notas' => 'nullable|string',
        ]);

        $client = Client::where('rfc', $validated['rfc'])->first();

        if (!$client) {
            return response()->json([
                'error' => 'Cliente no encontrado',
                'rfc' => $validated['rfc'],
            ], 404);
        }

        $operacion = new Operacion([
            'client_id' => $client->id,
            'tipo_actividad_vulnerable' => $validated['tipo_actividad_vulnerable'],
            'descripcion' => $validated['descripcion'] ?? null,
            'monto' => $validated['monto'],
            'moneda' => $validated['moneda'] ?? 'MXN',
            'forma_pago' => $validated['forma_pago'] ?? 'transferencia',
            'monto_efectivo' => $validated['monto_efectivo'] ?? 0,
            'contraparte_nombre' => $validated['contraparte_nombre'] ?? $client->razon_social,
            'contraparte_rfc' => $validated['contraparte_rfc'] ?? $client->rfc,
            'fecha_operacion' => $validated['fecha_operacion'] ?? now(),
            'estado' => 'registrada',
            'notas' => $validated['notas'] ?? null,
        ]);

        $operacion->folio = Operacion::generarFolio();
        $operacion->calcularUmbrales();
        $operacion->save();

        AuditLog::registrar(
            accion: 'operacion_creada',
            clientId: $client->id,
            entidadTipo: 'Operacion',
            entidadId: $operacion->id,
            datosNuevos: $operacion->toArray(),
            canal: 'n8n',
        );

        return response()->json([
            'success' => true,
            'folio' => $operacion->folio,
            'umas_equivalente' => $operacion->umas_equivalente,
            'supera_umbral_identificacion' => $operacion->supera_umbral_identificacion,
            'supera_umbral_aviso' => $operacion->supera_umbral_aviso,
            'supera_umbral_efectivo' => $operacion->supera_umbral_efectivo,
        ], 201);
    }

    /**
     * n8n → Laravel: Crear resultado de screening desde workflow.
     */
    public function screening(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'client_id' => 'required_without:rfc|nullable|exists:clients,id',
            'rfc' => 'required_without:client_id|nullable|string|max:13',
            'lista_tipo' => 'required|string|in:' . implode(',', array_keys(ScreeningResult::LISTAS)),
            'match_score' => 'required|numeric|between:0,100',
            'match_nombre' => 'required|string|max:255',
            'match_rfc' => 'nullable|string|max:13',
            'match_data' => 'nullable|array',
            'requiere_aviso_24h' => 'sometimes|boolean',
            'notas' => 'nullable|string',
        ]);

        // Resolve client by ID or RFC
        if (!empty($validated['client_id'])) {
            $client = Client::find($validated['client_id']);
        } else {
            $client = Client::where('rfc', $validated['rfc'])->first();
        }

        if (!$client) {
            return response()->json([
                'error' => 'Cliente no encontrado',
                'rfc' => $validated['rfc'] ?? null,
                'client_id' => $validated['client_id'] ?? null,
            ], 404);
        }

        $result = ScreeningResult::create([
            'client_id' => $client->id,
            'lista_tipo' => $validated['lista_tipo'],
            'match_score' => $validated['match_score'],
            'match_nombre' => $validated['match_nombre'],
            'match_rfc' => $validated['match_rfc'] ?? null,
            'match_data' => $validated['match_data'] ?? null,
            'estado' => 'pendiente_revision',
            'requiere_aviso_24h' => $validated['requiere_aviso_24h'] ?? false,
            'notas' => $validated['notas'] ?? null,
        ]);

        // Update client screening status
        $client->update([
            'screening_status' => 'pendiente_revision',
            'last_screened_at' => now(),
        ]);

        // Recalculate semaphore
        $client->calcularSemaforo();

        AuditLog::registrar(
            accion: 'screening_creado',
            clientId: $client->id,
            entidadTipo: 'ScreeningResult',
            entidadId: $result->id,
            datosNuevos: $result->toArray(),
            canal: 'n8n',
        );

        return response()->json([
            'success' => true,
            'screening_id' => $result->id,
            'estado' => $result->estado,
            'match_score' => $result->match_score,
            'requiere_aviso_24h' => $result->requiere_aviso_24h,
            'compliance_status' => $client->fresh()->compliance_status,
        ], 201);
    }
}
