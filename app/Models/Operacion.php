<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Operacion extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'operaciones';

    /**
     * UMA diario 2026 publicado por INEGI.
     */
    const UMA_DIARIO_2026 = 117.31;

    /**
     * Umbrales de identificación por tipo de actividad vulnerable (Art. 17 LFPIORPI).
     * Formato: fracción => [umbral_identificacion_umas, umbral_aviso_umas]
     *
     * Nota: El umbral de efectivo es siempre 645 UMAs (Art. 32 Reglamento).
     */
    const UMBRALES_POR_ACTIVIDAD = [
        'I'    => ['identificacion' => 325,  'aviso' => 645],    // Juegos con apuestas
        'II'   => ['identificacion' => 645,  'aviso' => 645],    // Tarjetas de servicios/crédito
        'III'  => ['identificacion' => 645,  'aviso' => 645],    // Traslado o custodia de dinero
        'IV'   => ['identificacion' => 1605, 'aviso' => 3210],   // Blindaje de vehículos
        'V'    => ['identificacion' => 645,  'aviso' => 645],    // Traslado de valores
        'VI'   => ['identificacion' => 1605, 'aviso' => 3210],   // Gestión, asesoría, consultoría
        'VII'  => ['identificacion' => 8025, 'aviso' => 16050],  // Construcción/desarrollo inmobiliario
        'VIII' => ['identificacion' => 805,  'aviso' => 1605],   // Joyas, metales, piedras preciosas
        'IX'   => ['identificacion' => 3210, 'aviso' => 6420],   // Compraventa de vehículos
        'X'    => ['identificacion' => 645,  'aviso' => 645],    // Traslado de divisas/cambio de moneda
        'XI'   => ['identificacion' => 645,  'aviso' => 805],    // Tarjetas prepago
        'XII'  => ['identificacion' => 1605, 'aviso' => 3210],   // Fideicomisos
        'XIII' => ['identificacion' => 1605, 'aviso' => 3210],   // Servicios notariales/fedatario
        'XIV'  => ['identificacion' => 1605, 'aviso' => 3210],   // Servicios profesionales independientes
    ];

    /**
     * Umbral de efectivo: cualquier operación con pago en efectivo >= 645 UMAs
     * debe reportarse (Art. 32 del Reglamento LFPIORPI).
     */
    const UMBRAL_EFECTIVO_UMAS = 645;

    const ACTIVIDADES_VULNERABLES = [
        'I'    => 'Juegos con apuestas, concursos o sorteos',
        'II'   => 'Emisión o comercialización de tarjetas de servicios',
        'III'  => 'Traslado o custodia de dinero o valores',
        'IV'   => 'Blindaje de vehículos',
        'V'    => 'Traslado de valores',
        'VI'   => 'Prestación de servicios de gestión, asesoría y consultoría',
        'VII'  => 'Servicios de construcción o desarrollo inmobiliario',
        'VIII' => 'Comercialización de joyas, metales preciosos, piedras preciosas y obras de arte',
        'IX'   => 'Compraventa de vehículos',
        'X'    => 'Servicios de traslado de divisas o cambio de moneda',
        'XI'   => 'Emisión y comercialización de tarjetas de crédito o prepago',
        'XII'  => 'Constitución de fideicomisos',
        'XIII' => 'Servicios notariales o de fedatario público',
        'XIV'  => 'Servicios profesionales independientes',
    ];

    protected $fillable = [
        'client_id',
        'user_id',
        'folio',
        'tipo_actividad_vulnerable',
        'descripcion',
        'monto',
        'moneda',
        'forma_pago',
        'monto_efectivo',
        'umas_equivalente',
        'supera_umbral_identificacion',
        'supera_umbral_aviso',
        'supera_umbral_efectivo',
        'contraparte_nombre',
        'contraparte_rfc',
        'fecha_operacion',
        'estado',
        'aviso_id',
        'notas',
    ];

    protected $casts = [
        'monto' => 'decimal:2',
        'monto_efectivo' => 'decimal:2',
        'supera_umbral_identificacion' => 'boolean',
        'supera_umbral_aviso' => 'boolean',
        'supera_umbral_efectivo' => 'boolean',
        'fecha_operacion' => 'datetime',
    ];

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function avisoSppld(): BelongsTo
    {
        return $this->belongsTo(AvisoSppld::class, 'aviso_id');
    }

    // -------------------------------------------------------------------------
    // Business logic
    // -------------------------------------------------------------------------

    /**
     * Calcula los umbrales UMA para esta operación según su tipo de actividad
     * vulnerable y actualiza los campos booleanos correspondientes.
     */
    public function calcularUmbrales(): void
    {
        if (!$this->monto) {
            return;
        }

        $this->umas_equivalente = (int) round($this->monto / self::UMA_DIARIO_2026);

        $umbrales = self::UMBRALES_POR_ACTIVIDAD[$this->tipo_actividad_vulnerable] ?? null;

        if ($umbrales) {
            $this->supera_umbral_identificacion = $this->umas_equivalente >= $umbrales['identificacion'];
            $this->supera_umbral_aviso = $this->umas_equivalente >= $umbrales['aviso'];
        }

        $this->verificarLimiteEfectivo();
    }

    /**
     * Verifica si el monto en efectivo supera el umbral de 645 UMAs.
     */
    public function verificarLimiteEfectivo(): void
    {
        if ($this->monto_efectivo) {
            $umasEfectivo = (int) round($this->monto_efectivo / self::UMA_DIARIO_2026);
            $this->supera_umbral_efectivo = $umasEfectivo >= self::UMBRAL_EFECTIVO_UMAS;
        } else {
            $this->supera_umbral_efectivo = false;
        }
    }

    // -------------------------------------------------------------------------
    // Scopes
    // -------------------------------------------------------------------------

    /**
     * Operaciones de un periodo mes/año específico.
     */
    public function scopeDelPeriodo($query, int $mes, int $anio)
    {
        return $query->whereMonth('fecha_operacion', $mes)
                     ->whereYear('fecha_operacion', $anio);
    }

    /**
     * Operaciones que superan el umbral de aviso al SAT.
     */
    public function scopeSuperanUmbralAviso($query)
    {
        return $query->where('supera_umbral_aviso', true);
    }

    /**
     * Operaciones que superan el umbral de efectivo.
     */
    public function scopeSuperanUmbralEfectivo($query)
    {
        return $query->where('supera_umbral_efectivo', true);
    }

    // -------------------------------------------------------------------------
    // Static helpers
    // -------------------------------------------------------------------------

    /**
     * Genera un folio único con formato OP-YYYY-NNNNN.
     */
    public static function generarFolio(): string
    {
        $anio = now()->format('Y');
        $ultimo = static::withTrashed()->whereYear('created_at', $anio)->count() + 1;

        return sprintf('OP-%s-%05d', $anio, $ultimo);
    }
}
