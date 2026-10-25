<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('legal_documents', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('short_name')->nullable();
            $table->string('slug')->unique();

            $table->string('practice_area')->index();
            $table->string('automation_phase')->index();

            $table->string('emoji', 16)->nullable();
            $table->string('tagline')->nullable()->comment('Promesa en una línea, para tarjetas');
            $table->text('summary')->nullable();
            $table->longText('body')->nullable()->comment('Contenido largo de la landing SEO');

            $table->string('legal_basis')->nullable()->comment('Ley o decreto que lo sustenta');

            // El precio base vive en el plan; aquí sólo el ajuste por complejidad.
            $table->integer('price_modifier')->default(0);
            $table->boolean('available_express')->default(true);
            $table->boolean('supports_signature')->default(false);
            $table->boolean('requires_lawyer_review')->default(true);

            $table->json('what_you_get')->nullable();
            $table->json('required_info')->nullable();
            $table->json('use_cases')->nullable();
            $table->json('not_included')->nullable();
            $table->json('faqs')->nullable();

            // SEO
            $table->string('seo_title')->nullable();
            $table->string('seo_description', 320)->nullable();
            $table->string('focus_keyword')->nullable();
            $table->json('seo_keywords')->nullable();
            $table->string('og_image_path')->nullable();

            $table->boolean('is_active')->default(true)->index();
            $table->boolean('is_featured')->default(false)->index();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->unsignedInteger('views_count')->default(0);
            $table->unsignedInteger('orders_count')->default(0);

            $table->timestamps();

            $table->index(['is_active', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('legal_documents');
    }
};
