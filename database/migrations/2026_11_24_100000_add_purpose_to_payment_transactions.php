<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Para qué se abrió cada cobro.
 *
 * Hace falta distinguirlos porque hay uno que no es un cobro de verdad: el
 * de verificación de tarjeta son $1.500 que se anulan en el mismo segundo,
 * y existe solo para saber si el banco aprueba. Sin esta columna, el webhook
 * lo trataría como un pago y activaría la suscripción con un cobro que
 * vamos a devolver.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payment_transactions', function (Blueprint $table) {
            $table->string('purpose', 20)->default('payment')->after('gateway')
                ->comment('payment | verification');

            $table->timestamp('voided_at')->nullable()->after('paid_at');
        });
    }

    public function down(): void
    {
        Schema::table('payment_transactions', function (Blueprint $table) {
            $table->dropColumn(['purpose', 'voided_at']);
        });
    }
};
