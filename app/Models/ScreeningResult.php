<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScreeningResult extends Model
{
    use HasFactory;

    const LISTAS = [
        'sat_69b_definitivo' => 'SAT Art. 69-B (Definitivo)',
        'sat_69b_presunto'   => 'SAT Art. 69-B (Presunto)',
        'ofac_sdn'           => 'OFAC SDN List',
        'uif_bloqueados'     => 'UIF - Lista de Bloqueados',
        'pep'                => 'Personas Políticamente Expuestas',
        'otra'               => 'Otra Lista',
    ];

    const ESTADOS = [
        'pendiente_revision' => 'Pendiente de Revisión',
        'confirmado'         => 'Confirmado',
        'descartado'         => 'Descartado',
        'falso_positivo'     => 'Falso Positivo',
    ];

    protected $fillable = [
        'client_id',
        'lista_tipo',
        'match_score',
        'match_nombre',
        'match_rfc',
        'match_data',
        'estado',
        'revisado_por_user_id',
        'revisado_at',
        'requiere_aviso_24h',
        'notas',
    ];

    protected $casts = [
        'match_score' => 'decimal:2',
        'match_data' => 'array',
        'revisado_at' => 'datetime',
        'requiere_aviso_24h' => 'boolean',
    ];

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function revisadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'revisado_por_user_id');
    }

    // -------------------------------------------------------------------------
    // Scopes
    // -------------------------------------------------------------------------

    /**
     * Resultados pendientes de revisión por el oficial de cumplimiento.
     */
    public function scopePendientesRevision($query)
    {
        return $query->where('estado', 'pendiente_revision');
    }

    /**
     * Coincidencias confirmadas (match real contra una lista).
     */
    public function scopeConfirmados($query)
    {
        return $query->where('estado', 'confirmado');
    }

    /**
     * Resultados que requieren aviso al SAT en 24 horas.
     */
    public function scopeRequierenAviso24h($query)
    {
        return $query->where('requiere_aviso_24h', true)
                     ->where('estado', 'confirmado');
    }
}
