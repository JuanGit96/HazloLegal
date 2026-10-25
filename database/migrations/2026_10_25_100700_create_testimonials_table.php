<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('testimonials', function (Blueprint $table) {
            $table->id();
            $table->string('author_name');
            $table->string('author_city')->nullable();
            $table->string('author_role')->nullable();
            $table->text('quote');
            $table->unsignedTinyInteger('rating')->default(5);
            $table->string('source')->default('whatsapp')->comment('whatsapp | google | email');
            $table->string('avatar_path')->nullable();
            $table->string('screenshot_path')->nullable();
            $table->string('outcome')->nullable()->comment('Resultado concreto, p. ej. "Cirugía autorizada en 6 días"');
            $table->foreignId('legal_document_id')->nullable()->constrained()->nullOnDelete();
            $table->boolean('is_published')->default(true)->index();
            $table->boolean('show_on_home')->default(false);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('testimonials');
    }
};
