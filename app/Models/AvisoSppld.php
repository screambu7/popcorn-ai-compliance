<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AvisoSppld extends Model
{
    use HasFactory;

    protected $table = 'avisos_sppld';

    const TIPOS = [
        'normal'       => 'Aviso Normal',
        'cero'         => 'Aviso en Cero',
        '24horas'      => 'Aviso 24 Horas',
        'modificatorio' => 'Aviso Modificatorio',
    ];

    const ESTADOS = [
        'borrador'  => 'Borrador',
        'generado'  => 'Generado',
        'enviado'   => 'Enviado',
        'aceptado'  => 'Aceptado',
        'rechazado' => 'Rechazado',
    ];

    protected $fillable = [
        'folio',
        'tipo',
        'periodo_mes',
        'periodo_anio',
        'fecha_generacion',
        'fecha_envio',
        'fecha_acuse',
        'xml_path',
        'acuse_path',
        'total_operaciones',
        'monto_total',
        'estado',
        'errores_sat',
        'generado_por_user_id',
        'aprobado_por_user_id',
        'notas',
    ];

    protected $casts = [
        'fecha_generacion' => 'datetime',
        'fecha_envio' => 'datetime',
        'fecha_acuse' => 'datetime',
        'monto_total' => 'decimal:2',
        'errores_sat' => 'array',
    ];

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function operaciones(): HasMany
    {
        return $this->hasMany(Operacion::class, 'aviso_id');
    }

    public function generadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'generado_por_user_id');
    }

    public function aprobadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'aprobado_por_user_id');
    }

    // -------------------------------------------------------------------------
    // Scopes
    // -------------------------------------------------------------------------

    /**
     * Avisos pendientes de envío (borrador o generado).
     */
    public function scopePendientes($query)
    {
        return $query->whereIn('estado', ['borrador', 'generado']);
    }

    /**
     * Avisos de un periodo mes/año específico.
     */
    public function scopeDelPeriodo($query, int $mes, int $anio)
    {
        return $query->where('periodo_mes', $mes)
                     ->where('periodo_anio', $anio);
    }

    /**
     * Avisos rechazados por el SAT.
     */
    public function scopeRechazados($query)
    {
        return $query->where('estado', 'rechazado');
    }

    // -------------------------------------------------------------------------
    // Static helpers
    // -------------------------------------------------------------------------

    /**
     * Genera un folio único con formato AV-YYYY-MM-NNNNN.
     */
    public static function generarFolio(): string
    {
        $now = now();
        $anio = $now->format('Y');
        $mes = $now->format('m');
        $ultimo = static::whereYear('created_at', $anio)
                        ->whereMonth('created_at', $now->month)
                        ->count() + 1;

        return sprintf('AV-%s-%s-%05d', $anio, $mes, $ultimo);
    }
}
