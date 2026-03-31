<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExpedienteAntilavado extends Model
{
    use HasFactory, SoftDeletes;

    const UMA_DIARIO_2026 = 117.31;
    const UMBRAL_AVISO_UMAS = 645;
    const UMBRAL_REPORTE_UMAS = 1500;
    const RETENCION_ANIOS = 10;

    const ESTADOS = [
        'pendiente' => 'Pendiente',
        'en_revision' => 'En Revisión',
        'completo' => 'Completo',
        'reportado_uif' => 'Reportado a UIF',
    ];

    const ACTIVIDADES_VULNERABLES = [
        'I' => 'Juegos con apuestas, concursos o sorteos',
        'II' => 'Emisión o comercialización de tarjetas de servicios',
        'III' => 'Traslado o custodia de dinero o valores',
        'IV' => 'Blindaje de vehículos',
        'V' => 'Traslado de valores',
        'VI' => 'Prestación de servicios de gestión, asesoría y consultoría',
        'VII' => 'Servicios de construcción o desarrollo inmobiliario',
        'VIII' => 'Comercialización de joyas, metales preciosos, piedras preciosas y obras de arte',
        'IX' => 'Compraventa de vehículos',
        'X' => 'Servicios de traslado de divisas o cambio de moneda',
        'XI' => 'Emisión y comercialización de tarjetas de crédito o prepago',
        'XII' => 'Constitución de fideicomisos',
        'XIII' => 'Servicios notariales o de fedatario público',
        'XIV' => 'Servicios profesionales independientes',
    ];

    protected $table = 'expedientes_antilavado';

    protected $fillable = [
        'client_id',
        'folio',
        'tipo_actividad_vulnerable',
        'descripcion_actividad',
        'monto_operacion',
        'moneda',
        'umas_equivalente',
        'supera_umbral_aviso',
        'supera_umbral_reporte',
        'nombre_beneficiario',
        'rfc_beneficiario',
        'es_pep',
        'nivel_riesgo',
        'documentos_identidad',
        'documentos_soporte',
        'estado',
        'fecha_operacion',
        'fecha_reporte_uif',
        'observaciones',
        'fecha_vencimiento_retencion',
    ];

    protected $casts = [
        'monto_operacion' => 'decimal:2',
        'supera_umbral_aviso' => 'boolean',
        'supera_umbral_reporte' => 'boolean',
        'es_pep' => 'boolean',
        'documentos_identidad' => 'array',
        'documentos_soporte' => 'array',
        'fecha_operacion' => 'datetime',
        'fecha_reporte_uif' => 'datetime',
        'fecha_vencimiento_retencion' => 'date',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function calcularUmbrales(): void
    {
        if ($this->monto_operacion) {
            $this->umas_equivalente = (int) round($this->monto_operacion / self::UMA_DIARIO_2026);
            $this->supera_umbral_aviso = $this->umas_equivalente >= self::UMBRAL_AVISO_UMAS;
            $this->supera_umbral_reporte = $this->umas_equivalente >= self::UMBRAL_REPORTE_UMAS;
        }
    }

    public function calcularRiesgo(): void
    {
        if ($this->es_pep || $this->supera_umbral_reporte) {
            $this->nivel_riesgo = 'alto';
        } elseif ($this->supera_umbral_aviso) {
            $this->nivel_riesgo = 'medio';
        } else {
            $this->nivel_riesgo = 'bajo';
        }
    }

    public function calcularRetencion(): void
    {
        if ($this->fecha_operacion) {
            $this->fecha_vencimiento_retencion = $this->fecha_operacion->addYears(self::RETENCION_ANIOS);
        }
    }

    public static function generarFolio(): string
    {
        $anio = now()->format('Y');
        $ultimo = static::whereYear('created_at', $anio)->count() + 1;
        return sprintf('KYC-%s-%05d', $anio, $ultimo);
    }

    public function scopePendientes($query)
    {
        return $query->where('estado', 'pendiente');
    }

    public function scopeAltoRiesgo($query)
    {
        return $query->where('nivel_riesgo', 'alto');
    }

    public function scopeRequiereReporteUif($query)
    {
        return $query->where(function ($q) {
            $q->where('supera_umbral_reporte', true)->orWhere('es_pep', true);
        });
    }
}
