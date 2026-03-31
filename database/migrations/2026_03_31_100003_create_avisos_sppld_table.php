<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Avisos al SAT a través del Sistema de Presentación de Avisos
     * ante la SHCP (SPPLD/Portal Antilavado).
     *
     * Art. 17 y 18 LFPIORPI: obligación de presentar avisos
     * sobre operaciones que superen umbrales.
     */
    public function up(): void
    {
        Schema::create('avisos_sppld', function (Blueprint $table) {
            $table->id();
            $table->string('folio')->unique();
            $table->enum('tipo', ['normal', 'cero', '24horas', 'modificatorio'])->default('normal');

            // Periodo del aviso
            $table->unsignedTinyInteger('periodo_mes');
            $table->unsignedSmallInteger('periodo_anio');

            // Fechas del ciclo de vida
            $table->timestamp('fecha_generacion')->useCurrent();
            $table->timestamp('fecha_envio')->nullable();
            $table->timestamp('fecha_acuse')->nullable();

            // Archivos
            $table->string('xml_path');
            $table->string('acuse_path')->nullable();

            // Resumen
            $table->unsignedInteger('total_operaciones')->default(0);
            $table->decimal('monto_total', 15, 2)->default(0);

            // Estado
            $table->enum('estado', ['borrador', 'generado', 'enviado', 'aceptado', 'rechazado'])->default('borrador');
            $table->json('errores_sat')->nullable();

            // Responsables
            $table->foreignId('generado_por_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('aprobado_por_user_id')->nullable()->constrained('users')->nullOnDelete();

            $table->text('notas')->nullable();

            $table->timestamps();

            // Índices
            $table->index(['periodo_mes', 'periodo_anio']);
            $table->index('estado');
            $table->index('tipo');
        });

        // Agregar FK de operaciones -> avisos ahora que la tabla existe
        Schema::table('operaciones', function (Blueprint $table) {
            $table->foreign('aviso_id')->references('id')->on('avisos_sppld')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('operaciones', function (Blueprint $table) {
            $table->dropForeign(['aviso_id']);
        });

        Schema::dropIfExists('avisos_sppld');
    }
};
