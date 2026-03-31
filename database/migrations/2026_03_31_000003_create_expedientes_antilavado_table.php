<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Expedientes de conocimiento del cliente (KYC) requeridos por la
     * Ley Federal para la Prevención e Identificación de Operaciones
     * con Recursos de Procedencia Ilícita (LFPIORPI) — Art. 17.
     *
     * Retención obligatoria: 10 años (Art. 18 LFPIORPI).
     */
    public function up(): void
    {
        Schema::create('expedientes_antilavado', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->string('folio')->unique(); // folio interno ej. KYC-2026-00001
            $table->string('tipo_actividad_vulnerable'); // clave actividad Art.17
            $table->text('descripcion_actividad')->nullable();

            // Umbrales — UMA 2026: $117.31/día
            $table->decimal('monto_operacion', 15, 2)->nullable();
            $table->string('moneda', 3)->default('MXN');
            $table->integer('umas_equivalente')->nullable(); // monto / UMA diario
            $table->boolean('supera_umbral_aviso')->default(false);  // 645 UMAs
            $table->boolean('supera_umbral_reporte')->default(false); // 1500 UMAs → reporte a UIF

            // KYC básico
            $table->string('nombre_beneficiario')->nullable();
            $table->string('rfc_beneficiario', 13)->nullable();
            $table->boolean('es_pep')->default(false);
            $table->string('nivel_riesgo')->default('bajo'); // bajo, medio, alto

            // Documentos (rutas en storage)
            $table->json('documentos_identidad')->nullable();
            $table->json('documentos_soporte')->nullable();

            // Estado del expediente
            $table->string('estado')->default('pendiente'); // pendiente, completo, reportado_uif
            $table->timestamp('fecha_operacion')->nullable();
            $table->timestamp('fecha_reporte_uif')->nullable();
            $table->text('observaciones')->nullable();

            // Retención 10 años — no eliminar antes de esta fecha
            $table->date('fecha_vencimiento_retencion')->nullable();

            $table->timestamps();
            $table->softDeletes(); // solo borrado lógico por retención obligatoria

            $table->index('client_id');
            $table->index('estado');
            $table->index('fecha_operacion');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expedientes_antilavado');
    }
};
