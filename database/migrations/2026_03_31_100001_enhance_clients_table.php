<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Campos adicionales requeridos por LFPIORPI para el expediente
     * de identificación de clientes (Art. 12, 13 y 14 del Reglamento).
     */
    public function up(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            // Tipo de persona
            $table->enum('tipo_persona', ['fisica', 'moral'])->default('moral')->after('razon_social');

            // Datos persona física
            $table->string('curp', 18)->nullable()->after('rfc');
            $table->string('nacionalidad', 100)->default('Mexicana')->after('curp');
            $table->string('pais_nacimiento', 100)->nullable()->after('nacionalidad');
            $table->date('fecha_nacimiento')->nullable()->after('pais_nacimiento');
            $table->enum('genero', ['M', 'F'])->nullable()->after('fecha_nacimiento');

            // Domicilio
            $table->string('domicilio_calle')->nullable()->after('telefono');
            $table->string('domicilio_num_ext', 20)->nullable()->after('domicilio_calle');
            $table->string('domicilio_num_int', 20)->nullable()->after('domicilio_num_ext');
            $table->string('domicilio_colonia')->nullable()->after('domicilio_num_int');
            $table->string('domicilio_cp', 5)->nullable()->after('domicilio_colonia');
            $table->string('domicilio_municipio')->nullable()->after('domicilio_cp');
            $table->string('domicilio_estado')->nullable()->after('domicilio_municipio');
            $table->string('domicilio_pais', 100)->default('México')->after('domicilio_estado');

            // Actividad económica (SCIAN)
            $table->string('actividad_economica_scian', 10)->nullable()->after('domicilio_pais');

            // Datos persona moral
            $table->text('objeto_social')->nullable()->after('actividad_economica_scian');
            $table->string('representante_legal_nombre')->nullable()->after('objeto_social');
            $table->string('representante_legal_rfc', 13)->nullable()->after('representante_legal_nombre');

            // Propietario real / beneficiario controlador
            $table->string('propietario_real_nombre')->nullable()->after('representante_legal_rfc');
            $table->string('propietario_real_rfc', 13)->nullable()->after('propietario_real_nombre');
            $table->decimal('propietario_real_porcentaje', 5, 2)->nullable()->after('propietario_real_rfc');

            // Datos constitución (persona moral)
            $table->date('fecha_constitucion')->nullable()->after('propietario_real_porcentaje');
            $table->string('numero_escritura', 50)->nullable()->after('fecha_constitucion');

            // Semáforo de cumplimiento
            $table->enum('compliance_status', ['verde', 'amarillo', 'rojo'])->default('verde')->after('nivel_riesgo');

            // Screening (listas negras / PEP)
            $table->string('screening_status', 50)->default('pendiente')->after('compliance_status');
            $table->timestamp('last_screened_at')->nullable()->after('screening_status');

            // Vencimiento de documentos
            $table->date('documents_expire_at')->nullable()->after('last_screened_at');

            // Índices
            $table->index('tipo_persona');
            $table->index('compliance_status');
            $table->index('screening_status');
        });
    }

    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropIndex(['tipo_persona']);
            $table->dropIndex(['compliance_status']);
            $table->dropIndex(['screening_status']);

            $table->dropColumn([
                'tipo_persona',
                'curp',
                'nacionalidad',
                'pais_nacimiento',
                'fecha_nacimiento',
                'genero',
                'domicilio_calle',
                'domicilio_num_ext',
                'domicilio_num_int',
                'domicilio_colonia',
                'domicilio_cp',
                'domicilio_municipio',
                'domicilio_estado',
                'domicilio_pais',
                'actividad_economica_scian',
                'objeto_social',
                'representante_legal_nombre',
                'representante_legal_rfc',
                'propietario_real_nombre',
                'propietario_real_rfc',
                'propietario_real_porcentaje',
                'fecha_constitucion',
                'numero_escritura',
                'compliance_status',
                'screening_status',
                'last_screened_at',
                'documents_expire_at',
            ]);
        });
    }
};
