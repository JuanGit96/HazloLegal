<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Las dos formas de entrar a la suscripción.
 *
 * Con enlace de invitación: unos meses gratis, para los abogados que traemos
 * a mano y de los que esperamos que llenen el directorio al principio.
 *
 * Sin enlace: el precio de lanzamiento, más barato durante unos meses. No es
 * una rebaja escondida —el precio de lista se muestra tachado al lado— y
 * existe porque una red con pocos abogados todavía no vale lo que valdrá.
 *
 * En los dos casos se registra la tarjeta antes de empezar. Un abogado que
 * se va el día del primer cobro no era un abogado suscrito, era un mes de
 * perfil publicado gratis.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscription_promos', function (Blueprint $table) {
            $table->id();

            // Sin código es la oferta que recibe quien llega por su cuenta;
            // solo puede haber una marcada por defecto a la vez.
            $table->string('code', 40)->nullable()->unique();
            $table->boolean('is_default')->default(false)->index();

            $table->string('name');
            $table->string('tagline')->nullable()->comment('La frase que ve el abogado');
            $table->text('terms')->nullable()->comment('La letra pequeña: qué pasa cuando se acaba');

            $table->unsignedTinyInteger('free_months')->default(0);
            $table->unsignedTinyInteger('discount_percentage')->default(0);
            $table->unsignedTinyInteger('discount_months')->default(0);

            // Atada a un plan o válida para todos.
            $table->foreignId('lawyer_plan_id')->nullable()->constrained()->nullOnDelete();

            $table->timestamp('starts_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->unsignedInteger('max_redemptions')->nullable();
            $table->unsignedInteger('redeemed_count')->default(0);

            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::table('lawyer_subscriptions', function (Blueprint $table) {
            $table->foreignId('subscription_promo_id')->nullable()->after('lawyer_plan_id')
                ->constrained()->nullOnDelete();
            // Copia del código, porque la promoción se puede desactivar y la
            // condición que se le prometió a este abogado no cambia por eso.
            $table->string('promo_code', 40)->nullable()->after('subscription_promo_id');

            $table->unsignedTinyInteger('discount_percentage')->default(0)->after('price');
            $table->timestamp('discount_ends_at')->nullable()->after('trial_ends_at');

            // La tarjeta queda en Wompi; aquí solo su identificador y los
            // cuatro últimos dígitos, que es lo que se le puede mostrar al
            // abogado para que reconozca cuál registró.
            $table->string('payment_source_id', 40)->nullable()->after('discount_ends_at');
            $table->string('card_brand', 20)->nullable()->after('payment_source_id');
            $table->string('card_last_four', 4)->nullable()->after('card_brand');
            $table->timestamp('card_validated_at')->nullable()->after('card_last_four');

            // La autorización del cobro recurrente es expresa y con fecha:
            // sin ella no se puede volver a cobrar esa tarjeta.
            $table->timestamp('recurring_authorized_at')->nullable()->after('card_validated_at');
        });
    }

    public function down(): void
    {
        Schema::table('lawyer_subscriptions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('subscription_promo_id');
            $table->dropColumn([
                'promo_code', 'discount_percentage', 'discount_ends_at',
                'payment_source_id', 'card_brand', 'card_last_four',
                'card_validated_at', 'recurring_authorized_at',
            ]);
        });

        Schema::dropIfExists('subscription_promos');
    }
};
