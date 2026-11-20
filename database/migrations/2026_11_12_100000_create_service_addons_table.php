<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Los «toppings» del negocio.
 *
 * El precio de un trámite ya no sale de un plan cerrado: es un precio base
 * (que puede ser cero) más los adicionales que el cliente va sumando. Esta
 * tabla es el catálogo global de esos adicionales; el precio real de cada
 * uno por trámite vive en la tabla pivote.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_addons', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique()->comment('express | instructivo | acompanamiento | firma_digital | sesion');
            $table->string('name');
            $table->string('short_name')->nullable();
            $table->string('emoji', 16)->nullable();

            $table->string('kind')->index()->comment('delivery | guidance | review | signature | session');

            $table->text('tagline')->nullable()->comment('Una línea: qué gana el cliente');
            $table->text('description')->nullable();
            $table->json('bullets')->nullable()->comment('Lo que incluye, para la tarjeta de venta');

            $table->unsignedInteger('default_price')->default(0);

            // Persuasión, toda editable desde el panel.
            $table->string('badge')->nullable()->comment('MÁS ELEGIDO, RECOMENDADO…');
            $table->string('social_proof')->nullable()->comment('«7 de cada 10 lo agregan»');
            $table->unsignedTinyInteger('discount_percentage')->default(0)->comment('Para mostrar precio tachado');

            $table->boolean('is_active')->default(true)->index();
            $table->unsignedSmallInteger('sort_order')->default(0);

            $table->timestamps();
        });

        Schema::create('legal_document_service_addon', function (Blueprint $table) {
            $table->id();
            $table->foreignId('legal_document_id')->constrained()->cascadeOnDelete();
            $table->foreignId('service_addon_id')->constrained()->cascadeOnDelete();

            // Si es null se usa el precio por defecto del adicional.
            $table->unsignedInteger('price')->nullable();

            $table->boolean('is_available')->default(true);
            $table->boolean('is_recommended')->default(false)->comment('Se destaca visualmente');
            $table->boolean('is_preselected')->default(false)->comment('Llega marcado por defecto');
            $table->unsignedSmallInteger('sort_order')->default(0);

            $table->timestamps();

            $table->unique(['legal_document_id', 'service_addon_id'], 'doc_addon_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('legal_document_service_addon');
        Schema::dropIfExists('service_addons');
    }
};
