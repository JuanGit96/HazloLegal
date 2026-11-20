<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Se van los planes cerrados.
 *
 * El precio ya no sale de un plan («documento», «asesoría»): sale del trámite
 * más los adicionales que el cliente elige. Mantener la tabla sólo dejaría dos
 * fuentes de verdad para lo mismo, que es la forma más segura de cobrar mal.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('service_plan_id');
        });

        Schema::dropIfExists('service_plans');
    }

    public function down(): void
    {
        // Irreversible a propósito: reconstruir los planes exigiría inventar
        // los precios que tenían, y eso falsearía el histórico de pedidos.
    }
};
