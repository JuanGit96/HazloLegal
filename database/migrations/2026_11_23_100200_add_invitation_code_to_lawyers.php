<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Con qué invitación llegó un abogado.
 *
 * Se guarda en el registro y no solo en la sesión porque entre que se
 * inscribe y paga pasa la verificación de su tarjeta profesional, que es a
 * mano y tarda. Si la condición viviera en una cookie, el abogado invitado
 * acabaría pagando precio de lista sin entender por qué.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lawyers', function (Blueprint $table) {
            $table->string('invitation_code', 40)->nullable()->index()->after('email');
        });
    }

    public function down(): void
    {
        Schema::table('lawyers', function (Blueprint $table) {
            $table->dropColumn('invitation_code');
        });
    }
};
