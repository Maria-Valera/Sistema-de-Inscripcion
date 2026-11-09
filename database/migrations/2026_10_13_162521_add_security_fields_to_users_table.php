<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'rol_principal')) {
                $table->string('rol_principal', 50)->nullable()->after('persona_id');
            }

            if (!Schema::hasColumn('users', 'debe_cambiar_password')) {
                $table->boolean('debe_cambiar_password')->default(true)->after('rol_principal');
            }

            if (!Schema::hasColumn('users', 'activo')) {
                $table->boolean('activo')->default(true)->after('debe_cambiar_password');
            }

            if (!Schema::hasColumn('users', 'ultimo_acceso_at')) {
                $table->timestamp('ultimo_acceso_at')->nullable()->after('activo');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'rol_principal')) {
                $table->dropColumn('rol_principal');
            }

            if (Schema::hasColumn('users', 'debe_cambiar_password')) {
                $table->dropColumn('debe_cambiar_password');
            }

            if (Schema::hasColumn('users', 'activo')) {
                $table->dropColumn('activo');
            }

            if (Schema::hasColumn('users', 'ultimo_acceso_at')) {
                $table->dropColumn('ultimo_acceso_at');
            }
        });
    }
};
