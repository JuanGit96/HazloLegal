<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Pasa el catálogo del modelo «planes» al modelo «base + adicionales».
 *
 * Algunos trámites arrancan gratis (base_price = 0) porque su función es
 * atraer: el documento gratuito es el imán, y el dinero entra por los
 * adicionales, por la suscripción de abogados y por el dato del lead.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('legal_documents', function (Blueprint $table) {
            $table->unsignedInteger('base_price')->default(0)->after('automation_phase');
            $table->unsignedInteger('compare_at_price')->nullable()->after('base_price')
                ->comment('Precio tachado para anclar; si es null se calcula con el descuento');
            $table->unsignedTinyInteger('discount_percentage')->default(0)->after('compare_at_price');

            // «No existe en autoservicio»: la tutela solo se vende acompañada.
            $table->boolean('self_service_enabled')->default(true)->after('discount_percentage');
            $table->boolean('is_free')->default(false)->after('self_service_enabled')->index();

            $table->unsignedSmallInteger('delivery_hours')->default(24)->after('is_free');
            $table->unsignedSmallInteger('express_delivery_hours')->default(0)
                ->after('delivery_hours')->comment('0 = inmediato');

            $table->string('hook')->nullable()->after('tagline')
                ->comment('Gancho comercial corto para tarjetas y anuncios');
        });
    }

    public function down(): void
    {
        Schema::table('legal_documents', function (Blueprint $table) {
            $table->dropColumn([
                'base_price', 'compare_at_price', 'discount_percentage',
                'self_service_enabled', 'is_free',
                'delivery_hours', 'express_delivery_hours', 'hook',
            ]);
        });
    }
};
