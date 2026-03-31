<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * RBAC básico para cumplimiento LFPIORPI.
     * - admin: acceso total
     * - oficial_cumplimiento: revisa screenings, aprueba avisos, gestiona expedientes
     * - operador: registra operaciones y clientes
     * - auditor: solo lectura con acceso a audit logs
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['admin', 'oficial_cumplimiento', 'operador', 'auditor'])
                ->default('operador')
                ->after('password');
            $table->boolean('is_active')->default(true)->after('role');

            $table->index('role');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['role']);
            $table->dropColumn(['role', 'is_active']);
        });
    }
};
