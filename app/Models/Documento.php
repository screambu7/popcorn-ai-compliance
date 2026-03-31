<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Documento extends Model
{
    use HasFactory, SoftDeletes;

    const TIPOS = [
        'ine_frente'           => 'INE (Frente)',
        'ine_reverso'          => 'INE (Reverso)',
        'pasaporte'            => 'Pasaporte',
        'curp'                 => 'CURP',
        'comprobante_domicilio' => 'Comprobante de Domicilio',
        'constancia_fiscal'    => 'Constancia de Situación Fiscal',
        'acta_constitutiva'    => 'Acta Constitutiva',
        'poder_notarial'       => 'Poder Notarial',
        'comprobante_ingresos' => 'Comprobante de Ingresos',
        'otro'                 => 'Otro',
    ];

    protected $fillable = [
        'client_id',
        'expediente_id',
        'tipo',
        'nombre_archivo',
        'ruta_archivo',
        'mime_type',
        'tamano_bytes',
        'fecha_emision',
        'fecha_vencimiento',
        'verificado',
        'verificado_por_user_id',
        'verificado_at',
        'notas',
    ];

    protected $casts = [
        'fecha_emision' => 'date',
        'fecha_vencimiento' => 'date',
        'verificado' => 'boolean',
        'verificado_at' => 'datetime',
    ];

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function expediente(): BelongsTo
    {
        return $this->belongsTo(ExpedienteAntilavado::class, 'expediente_id');
    }

    public function verificadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verificado_por_user_id');
    }

    // -------------------------------------------------------------------------
    // Accessors
    // -------------------------------------------------------------------------

    /**
     * Indica si el documento está vencido.
     */
    protected function isVencido(): Attribute
    {
        return Attribute::make(
            get: function () {
                if (!$this->fecha_vencimiento) {
                    return false;
                }

                return $this->fecha_vencimiento->isPast();
            },
        );
    }

    // -------------------------------------------------------------------------
    // Scopes
    // -------------------------------------------------------------------------

    /**
     * Documentos cuya fecha de vencimiento ya pasó.
     */
    public function scopeVencidos($query)
    {
        return $query->whereNotNull('fecha_vencimiento')
                     ->where('fecha_vencimiento', '<', now()->toDateString());
    }

    /**
     * Documentos que vencen dentro de los próximos $dias días.
     */
    public function scopePorVencer($query, int $dias = 30)
    {
        return $query->whereNotNull('fecha_vencimiento')
                     ->where('fecha_vencimiento', '>=', now()->toDateString())
                     ->where('fecha_vencimiento', '<=', now()->addDays($dias)->toDateString());
    }

    /**
     * Documentos no verificados.
     */
    public function scopeSinVerificar($query)
    {
        return $query->where('verificado', false);
    }
}
