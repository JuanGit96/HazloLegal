<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('post_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('emoji', 16)->nullable();
            $table->text('description')->nullable();
            $table->string('seo_title')->nullable();
            $table->string('seo_description', 320)->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('excerpt')->nullable();
            $table->longText('body')->nullable();

            $table->string('cover_path')->nullable();
            $table->string('cover_alt')->nullable();

            $table->foreignId('post_category_id')->nullable()->constrained()->nullOnDelete();

            // Autoría verificable: Google exige E-E-A-T en contenido legal (YMYL).
            $table->foreignId('author_lawyer_id')->nullable()->constrained('lawyers')->nullOnDelete();
            $table->foreignId('related_document_id')->nullable()->constrained('legal_documents')->nullOnDelete();

            $table->unsignedSmallInteger('reading_minutes')->default(4);
            $table->string('status')->default('draft')->index();
            $table->timestamp('published_at')->nullable()->index();
            $table->timestamp('reviewed_at')->nullable();

            $table->string('seo_title')->nullable();
            $table->string('seo_description', 320)->nullable();
            $table->string('focus_keyword')->nullable();
            $table->json('seo_keywords')->nullable();

            // Imán de leads: plantilla gratis descargable a cambio del correo.
            $table->string('lead_magnet_title')->nullable();
            $table->string('lead_magnet_path')->nullable();

            $table->boolean('is_featured')->default(false);
            $table->unsignedInteger('views_count')->default(0);

            $table->timestamps();

            $table->index(['status', 'published_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('posts');
        Schema::dropIfExists('post_categories');
    }
};
