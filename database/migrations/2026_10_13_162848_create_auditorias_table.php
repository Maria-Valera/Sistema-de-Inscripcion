<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('auditorias', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->string('usuario_email')->nullable();
            $table->string('accion')->index();
            $table->string('auditable_type')->nullable();
            $table->unsignedBigInteger('auditable_id')->nullable();
            $table->json('valores_anteriores')->nullable();
            $table->json('valores_nuevos')->nullable();
            $table->text('descripcion')->nullable();
            $table->string('ip')->nullable();
            $table->text('user_agent')->nullable();
            $table->string('url')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });

        // Crear triggers para hacer la tabla inmutable
        DB::unprepared('
            CREATE TRIGGER prevent_auditorias_update
            BEFORE UPDATE ON auditorias
            FOR EACH ROW
            BEGIN
                SIGNAL SQLSTATE "45000"
                SET MESSAGE_TEXT = "No se permite actualizar registros en la tabla de auditorias. La tabla es de solo lectura.";
            END;
        ');

        DB::unprepared('
            CREATE TRIGGER prevent_auditorias_delete
            BEFORE DELETE ON auditorias
            FOR EACH ROW
            BEGIN
                SIGNAL SQLSTATE "45000"
                SET MESSAGE_TEXT = "No se permite eliminar registros en la tabla de auditorias. La tabla es de solo lectura.";
            END;
        ');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Eliminar triggers primero
        DB::unprepared('DROP TRIGGER IF EXISTS prevent_auditorias_update');
        DB::unprepared('DROP TRIGGER IF EXISTS prevent_auditorias_delete');

        // Eliminar tabla
        Schema::dropIfExists('auditorias');
    }
};
