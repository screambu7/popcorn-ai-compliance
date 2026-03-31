<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'client_id',
        'user_id',
        'accion',
        'entidad_tipo',
        'entidad_id',
        'datos_anteriores',
        'datos_nuevos',
        'canal',
        'ip_address',
        'notas',
    ];

    protected $casts = [
        'datos_anteriores' => 'array',
        'datos_nuevos' => 'array',
        'created_at' => 'datetime',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function registrar(
        string $accion,
        ?int $clientId = null,
        ?string $entidadTipo = null,
        ?int $entidadId = null,
        ?array $datosAnteriores = null,
        ?array $datosNuevos = null,
        string $canal = 'sistema',
        ?string $notas = null,
    ): static {
        return static::create([
            'client_id' => $clientId,
            'user_id' => auth()->id(),
            'accion' => $accion,
            'entidad_tipo' => $entidadTipo,
            'entidad_id' => $entidadId,
            'datos_anteriores' => $datosAnteriores,
            'datos_nuevos' => $datosNuevos,
            'canal' => $canal,
            'ip_address' => request()->ip(),
            'notas' => $notas,
        ]);
    }
}
