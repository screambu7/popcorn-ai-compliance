<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Client extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'razon_social',
        'rfc',
        'email',
        'telefono',
        'regimen_fiscal',
        'regimen_fiscal_descripcion',
        'actividad_vulnerable',
        'actividades_vulnerables',
        'obligado_antilavado',
        'es_pep',
        'nivel_riesgo',
        'active',
    ];

    protected $casts = [
        'actividad_vulnerable' => 'boolean',
        'actividades_vulnerables' => 'array',
        'obligado_antilavado' => 'boolean',
        'es_pep' => 'boolean',
        'active' => 'boolean',
    ];

    public function expedientes(): HasMany
    {
        return $this->hasMany(ExpedienteAntilavado::class);
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class);
    }

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    public function scopeVulnerable($query)
    {
        return $query->where('actividad_vulnerable', true);
    }

    public function scopeAltoRiesgo($query)
    {
        return $query->where('nivel_riesgo', 'alto');
    }
}
