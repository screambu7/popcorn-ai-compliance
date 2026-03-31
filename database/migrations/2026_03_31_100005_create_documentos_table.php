<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Gestión documental para expedientes de identificación (Art. 12-14 Reglamento LFPIORPI).
     * Documentos vinculados a clientes y/o expedientes antilavado.
     */
    public function up(): void
    {
        Schema::create('documentos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('expediente_id')->nullable()->constrained('expedientes_antilavado')->nullOnDelete();

            $table->enum('tipo', [
                'ine_frente',
                'ine_reverso',
                'pasaporte',
                'curp',
                'comprobante_domicilio',
                'constancia_fiscal',
                'acta_constitutiva',
                'poder_notarial',
                'comprobante_ingresos',
                'otro',
            ]);

            $table->string('nombre_archivo');
            $table->string('ruta_archivo');
            $table->string('mime_type', 100);
            $table->unsignedInteger('tamano_bytes')->default(0);

            $table->date('fecha_emision')->nullable();
            $table->date('fecha_vencimiento')->nullable();

            // Verificación por oficial de cumplimiento
            $table->boolean('verificado')->default(false);
            $table->foreignId('verificado_por_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verificado_at')->nullable();

            $table->text('notas')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Índices
            $table->index('client_id');
            $table->index('expediente_id');
            $table->index('tipo');
            $table->index('fecha_vencimiento');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documentos');
    }
};
