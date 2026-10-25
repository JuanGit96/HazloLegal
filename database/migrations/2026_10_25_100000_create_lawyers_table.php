<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lawyers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('email')->unique();
            $table->string('phone')->nullable();
            $table->string('whatsapp')->nullable();
            $table->string('photo_path')->nullable();

            // Credenciales: indispensables para E-E-A-T y para la confianza del cliente.
            $table->string('professional_card')->nullable()->comment('Tarjeta profesional (T.P.)');
            $table->string('university')->nullable();
            $table->string('headline')->nullable();
            $table->text('bio')->nullable();
            $table->unsignedSmallInteger('years_experience')->default(0);
            $table->string('city')->default('Bogotá');

            $table->json('practice_areas')->nullable();
            $table->json('specialties')->nullable();

            $table->string('status')->default('active')->index();
            $table->boolean('accepts_cases')->default(true)->index();
            $table->unsignedSmallInteger('max_active_cases')->default(5);

            $table->decimal('rating', 2, 1)->default(5.0);
            $table->unsignedInteger('cases_resolved')->default(0);

            $table->boolean('is_author')->default(false)->comment('Puede firmar o revisar contenido del blog');
            $table->boolean('notify_email')->default(true);
            $table->boolean('notify_whatsapp')->default(false);

            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'accepts_cases']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lawyers');
    }
};
