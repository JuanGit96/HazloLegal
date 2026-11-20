<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Calculadoras legales: tráfico orgánico de altísima intención. Quien
 * calcula su liquidación tiene un problema laboral ahora mismo.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('calculators', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique()->comment('liquidacion | indemnizacion | horas_extras | intereses_mora | prima');
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('emoji', 16)->nullable();
            $table->text('intro')->nullable();
            $table->longText('body')->nullable()->comment('Contenido largo bajo la calculadora, para SEO');

            $table->json('config')->nullable()->comment('Parámetros del año: SMLMV, auxilio, topes');

            $table->foreignId('related_document_id')->nullable()
                ->constrained('legal_documents')->nullOnDelete();

            $table->string('seo_title')->nullable();
            $table->string('seo_description', 320)->nullable();
            $table->string('focus_keyword')->nullable();
            $table->json('faqs')->nullable();

            $table->boolean('captures_lead')->default(true)
                ->comment('Pide el correo para enviar el resultado en PDF');
            $table->boolean('is_active')->default(true)->index();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->unsignedInteger('uses_count')->default(0);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('calculators');
    }
};
