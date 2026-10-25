<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_plans', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique()->comment('document | advisory | complex');
            $table->string('name');
            $table->string('tagline')->nullable();
            $table->text('description')->nullable();

            $table->unsignedInteger('price')->nullable()->comment('COP; null = cotizar');
            $table->unsignedInteger('express_price')->nullable();
            $table->unsignedSmallInteger('delivery_hours')->nullable();
            $table->unsignedSmallInteger('express_delivery_hours')->nullable();

            $table->json('includes')->nullable();
            $table->json('express_includes')->nullable();

            $table->boolean('is_quote_only')->default(false);
            $table->boolean('is_highlighted')->default(false);
            $table->string('badge')->nullable();
            $table->string('cta_label')->default('Empezar');

            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_plans');
    }
};
