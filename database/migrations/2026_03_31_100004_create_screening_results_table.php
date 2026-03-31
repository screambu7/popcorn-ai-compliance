<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Resultados de screening contra listas negras, PEP, OFAC, SAT 69-B, UIF.
     * Cada coincidencia se registra para revisión por el oficial de cumplimiento.
     */
    public function up(): void
    {
        Schema::create('screening_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();

            $table->enum('lista_tipo', [
                'sat_69b_definitivo',
                'sat_69b_presunto',
                'ofac_sdn',
                'uif_bloqueados',
                'pep',
                'otra',
            ]);

            $table->decimal('match_score', 5, 2)->default(0); // 0-100 fuzzy match
            $table->string('match_nombre');
            $table->string('match_rfc', 13)->nullable();
            $table->json('match_data')->nullable(); // registro completo de la lista

            $table->enum('estado', [
                'pendiente_revision',
                'confirmado',
                'descartado',
                'falso_positivo',
            ])->default('pendiente_revision');

            $table->foreignId('revisado_por_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('revisado_at')->nullable();
            $table->boolean('requiere_aviso_24h')->default(false);

            $table->text('notas')->nullable();

            $table->timestamps();

            // Índices
            $table->index('client_id');
            $table->index('lista_tipo');
            $table->index('estado');
            $table->index('match_score');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('screening_results');
    }
};
