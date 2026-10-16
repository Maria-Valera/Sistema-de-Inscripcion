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
            $indexes = Schema::getIndexes('users');
            $hasIndex = collect($indexes)->contains(function ($index) {
                return $index['name'] === 'users_persona_id_unique';
            });

            if (!$hasIndex) {
                $table->unique('persona_id', 'users_persona_id_unique');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $indexes = Schema::getIndexes('users');
            $hasIndex = collect($indexes)->contains(function ($index) {
                return $index['name'] === 'users_persona_id_unique';
            });

            if ($hasIndex) {
                $table->dropUnique('users_persona_id_unique');
            }
        });
    }
};
