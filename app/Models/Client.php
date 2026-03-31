<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Client extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        // Datos base
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
        // LFPIORPI fields
        'tipo_persona',
        'curp',
        'nacionalidad',
        'pais_nacimiento',
        'fecha_nacimiento',
        'genero',
        // Domicilio
        'domicilio_calle',
        'domicilio_num_ext',
        'domicilio_num_int',
        'domicilio_colonia',
        'domicilio_cp',
        'domicilio_municipio',
        'domicilio_estado',
        'domicilio_pais',
        // Económico
        'actividad_economica_scian',
        // Persona moral
        'objeto_social',
        'representante_legal_nombre',
        'representante_legal_rfc',
        // Propietario real
        'propietario_real_nombre',
        'propietario_real_rfc',
        'propietario_real_porcentaje',
        // Constitución
        'fecha_constitucion',
        'numero_escritura',
        // Compliance
        'compliance_status',
        'screening_status',
        'last_screened_at',
        'documents_expire_at',
    ];

    protected $casts = [
        'actividad_vulnerable' => 'boolean',
        'actividades_vulnerables' => 'array',
        'obligado_antilavado' => 'boolean',
        'es_pep' => 'boolean',
        'active' => 'boolean',
        'fecha_nacimiento' => 'date',
        'fecha_constitucion' => 'date',
        'propietario_real_porcentaje' => 'decimal:2',
        'last_screened_at' => 'datetime',
        'documents_expire_at' => 'date',
    ];

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function expedientes(): HasMany
    {
        return $this->hasMany(ExpedienteAntilavado::class);
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class);
    }

    public function operaciones(): HasMany
    {
        return $this->hasMany(Operacion::class);
    }

    /**
     * Avisos SPPLD vinculados a través de las operaciones del cliente.
     */
    public function avisos(): HasManyThrough
    {
        return $this->hasManyThrough(
            AvisoSppld::class,
            Operacion::class,
            'client_id',   // FK en operaciones
            'id',          // PK en avisos_sppld
            'id',          // PK en clients
            'aviso_id'     // FK en operaciones -> avisos_sppld
        );
    }

    public function screeningResults(): HasMany
    {
        return $this->hasMany(ScreeningResult::class);
    }

    public function documentos(): HasMany
    {
        return $this->hasMany(Documento::class);
    }

    // -------------------------------------------------------------------------
    // Business logic
    // -------------------------------------------------------------------------

    /**
     * Calcula el semáforo de cumplimiento del cliente según reglas LFPIORPI:
     *
     * - ROJO: screening confirmado contra lista negra, aviso vencido no enviado
     * - AMARILLO: documentos vencidos, screening pendiente de revisión
     * - VERDE: todo en orden
     *
     * Actualiza compliance_status y retorna el valor.
     */
    public function calcularSemaforo(): string
    {
        // ROJO: coincidencia confirmada en screening
        $tieneScreeningConfirmado = $this->screeningResults()
            ->where('estado', 'confirmado')
            ->exists();

        if ($tieneScreeningConfirmado) {
            $this->compliance_status = 'rojo';
            $this->save();
            return 'rojo';
        }

        // ROJO: operaciones que superan umbral de aviso sin aviso enviado
        $tieneAvisoPendiente = $this->operaciones()
            ->where('supera_umbral_aviso', true)
            ->where('estado', 'registrada')
            ->where('fecha_operacion', '<', now()->subDays(30))
            ->exists();

        if ($tieneAvisoPendiente) {
            $this->compliance_status = 'rojo';
            $this->save();
            return 'rojo';
        }

        // AMARILLO: documentos vencidos
        $tieneDocsVencidos = $this->documentos()
            ->vencidos()
            ->exists();

        if ($tieneDocsVencidos) {
            $this->compliance_status = 'amarillo';
            $this->save();
            return 'amarillo';
        }

        // AMARILLO: screening pendiente de revisión
        $tieneScreeningPendiente = $this->screeningResults()
            ->where('estado', 'pendiente_revision')
            ->exists();

        if ($tieneScreeningPendiente) {
            $this->compliance_status = 'amarillo';
            $this->save();
            return 'amarillo';
        }

        // VERDE: todo bien
        $this->compliance_status = 'verde';
        $this->save();
        return 'verde';
    }

    // -------------------------------------------------------------------------
    // Scopes
    // -------------------------------------------------------------------------

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

    /**
     * Clientes con al menos un documento vencido.
     */
    public function scopeDocumentosVencidos($query)
    {
        return $query->whereHas('documentos', function ($q) {
            $q->vencidos();
        });
    }

    /**
     * Clientes por semáforo de cumplimiento.
     */
    public function scopeComplianceStatus($query, string $status)
    {
        return $query->where('compliance_status', $status);
    }
}
