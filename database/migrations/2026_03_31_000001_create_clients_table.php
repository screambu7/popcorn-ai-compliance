<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->string('razon_social');
            $table->string('rfc', 13)->unique();
            $table->string('email');
            $table->string('telefono', 20)->nullable();
            $table->string('regimen_fiscal', 10); // clave SAT ej. 601, 612
            $table->string('regimen_fiscal_descripcion')->nullable();
            $table->boolean('actividad_vulnerable')->default(false); // Art. 17 LFPIORPI
            $table->json('actividades_vulnerables')->nullable(); // array de claves
            $table->boolean('obligado_antilavado')->default(false);
            $table->boolean('es_pep')->default(false); // Persona Políticamente Expuesta
            $table->string('nivel_riesgo')->default('bajo'); // bajo, medio, alto
            $table->boolean('active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
