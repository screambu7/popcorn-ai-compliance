<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Registro de operaciones con actividades vulnerables (Art. 17 LFPIORPI).
     * Cada operación puede generar un aviso al SAT si supera los umbrales.
     */
    public function up(): void
    {
        Schema::create('operaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();

            $table->string('folio')->unique();
            $table->string('tipo_actividad_vulnerable'); // Art. 17 fracción I-XIV
            $table->text('descripcion');
            $table->decimal('monto', 15, 2);
            $table->string('moneda', 3)->default('MXN');
            $table->enum('forma_pago', ['efectivo', 'transferencia', 'cheque', 'tarjeta', 'otro'])->default('transferencia');
            $table->decimal('monto_efectivo', 15, 2)->nullable();

            // Umbrales UMA
            $table->integer('umas_equivalente')->default(0);
            $table->boolean('supera_umbral_identificacion')->default(false);
            $table->boolean('supera_umbral_aviso')->default(false);
            $table->boolean('supera_umbral_efectivo')->default(false);

            // Contraparte
            $table->string('contraparte_nombre')->nullable();
            $table->string('contraparte_rfc', 13)->nullable();

            // Fechas y estado
            $table->dateTime('fecha_operacion');
            $table->enum('estado', ['registrada', 'identificada', 'notificada', 'reportada'])->default('registrada');

            // Relación con aviso SAT
            $table->unsignedBigInteger('aviso_id')->nullable();

            $table->text('notas')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Índices
            $table->index('client_id');
            $table->index('tipo_actividad_vulnerable');
            $table->index('fecha_operacion');
            $table->index('estado');
            $table->index('aviso_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('operaciones');
    }
};
