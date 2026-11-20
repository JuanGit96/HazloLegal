<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lawyers', function (Blueprint $table) {
            // Página de perfil pública, que es parte de lo que se les vende.
            $table->string('firm_name')->nullable()->after('name');
            $table->string('logo_path')->nullable()->after('photo_path');
            $table->text('long_bio')->nullable()->after('bio');
            $table->json('services')->nullable()->after('specialties');
            $table->json('languages')->nullable()->after('services');
            $table->boolean('offers_virtual')->default(true)->after('languages');
            $table->boolean('offers_free_first_call')->default(false)->after('offers_virtual');
            $table->string('website')->nullable()->after('whatsapp');
            $table->string('linkedin')->nullable()->after('website');

            $table->decimal('public_rating', 2, 1)->default(0)->after('rating');
            $table->unsignedInteger('reviews_count')->default(0)->after('public_rating');
            $table->unsignedInteger('profile_views')->default(0)->after('reviews_count');
            $table->unsignedInteger('contact_clicks')->default(0)->after('profile_views');

            $table->boolean('is_published')->default(false)->after('is_author')
                ->comment('Visible en el directorio público');
            $table->boolean('is_featured')->default(false)->after('is_published');

            $table->string('seo_title')->nullable();
            $table->string('seo_description', 320)->nullable();

            $table->foreignId('user_id')->nullable()->after('id')
                ->constrained()->nullOnDelete()
                ->comment('Acceso al portal del abogado');
        });
    }

    public function down(): void
    {
        Schema::table('lawyers', function (Blueprint $table) {
            $table->dropConstrainedForeignId('user_id');
            $table->dropColumn([
                'firm_name', 'logo_path', 'long_bio', 'services', 'languages',
                'offers_virtual', 'offers_free_first_call', 'website', 'linkedin',
                'public_rating', 'reviews_count', 'profile_views', 'contact_clicks',
                'is_published', 'is_featured', 'seo_title', 'seo_description',
            ]);
        });
    }
};
