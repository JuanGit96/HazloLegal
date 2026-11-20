<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * La otra mitad del negocio: los abogados pagan por estar donde está la
 * demanda. El documento gratuito trae al ciudadano; el ciudadano con un
 * problema real es lo que el abogado quiere, y por eso se suscribe.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lawyer_plans', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('name');
            $table->string('tagline')->nullable();
            $table->text('description')->nullable();

            $table->unsignedInteger('price_monthly')->default(0);
            $table->unsignedInteger('price_annual')->nullable();
            $table->unsignedTinyInteger('free_trial_months')->default(0);

            $table->json('features')->nullable();
            $table->json('limits')->nullable()->comment('Topes: casos/mes, ciudades, especialidades…');

            // Qué desbloquea, para decidirlo en código sin leer el texto.
            $table->boolean('includes_profile_page')->default(true);
            $table->boolean('includes_pdf_placement')->default(false)->comment('Aparece en los instructivos');
            $table->boolean('includes_case_alerts')->default(true);
            $table->boolean('includes_lead_data')->default(false)->comment('Acceso a leads que autorizaron contacto');
            $table->boolean('includes_featured')->default(false);
            $table->boolean('includes_blog_placement')->default(false);
            $table->unsignedSmallInteger('directory_priority')->default(0);

            $table->string('badge')->nullable();
            $table->boolean('is_highlighted')->default(false);
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);

            $table->timestamps();
        });

        Schema::create('lawyer_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lawyer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('lawyer_plan_id')->constrained()->restrictOnDelete();

            $table->string('status')->default('trialing')->index()
                ->comment('trialing | active | past_due | cancelled');
            $table->string('billing_period')->default('monthly')->comment('monthly | annual');

            $table->unsignedInteger('price')->default(0)->comment('Lo que paga realmente');
            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('renews_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();

            // El destacado se cobra desde el primer mes aunque haya prueba.
            $table->timestamp('featured_until')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lawyer_subscriptions');
        Schema::dropIfExists('lawyer_plans');
    }
};
