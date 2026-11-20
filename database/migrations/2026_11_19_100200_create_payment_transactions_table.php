<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Cada intento de cobro contra la pasarela.
 *
 * Es una tabla aparte y no unas columnas en `orders` porque un pedido puede
 * tener varios intentos (uno rechazado, otro aprobado) y porque lo mismo
 * sirve para las suscripciones de abogados. `payable` apunta a lo que se
 * está pagando.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_transactions', function (Blueprint $table) {
            $table->id();

            $table->morphs('payable');

            $table->string('gateway')->default('wompi')->index();
            // La referencia que viaja a la pasarela. Lleva un sufijo aleatorio
            // porque Wompi no admite dos transacciones con la misma.
            $table->string('reference')->unique();
            $table->string('gateway_transaction_id')->nullable()->index();

            $table->unsignedBigInteger('amount_in_cents');
            $table->string('currency', 3)->default('COP');

            $table->string('status')->default('pending')->index()
                ->comment('pending | approved | declined | voided | error');
            $table->string('payment_method')->nullable();
            $table->string('status_message')->nullable();

            $table->string('customer_email')->nullable();
            $table->json('payload')->nullable()->comment('Último evento recibido, para auditar');

            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            $table->index(['payable_type', 'payable_id', 'status'], 'payment_payable_status_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_transactions');
    }
};
