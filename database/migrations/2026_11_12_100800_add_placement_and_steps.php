<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lawyers', function (Blueprint $table) {
            // Para repartir las apariciones en los instructivos con equidad.
            $table->timestamp('last_placement_at')->nullable()->after('contact_clicks');
            $table->unsignedInteger('placements_count')->default(0)->after('last_placement_at');
        });

        Schema::table('legal_documents', function (Blueprint $table) {
            $table->json('filing_steps')->nullable()->after('faqs')
                ->comment('Pasos de radicación que salen en el instructivo PDF');
            $table->string('filing_entity')->nullable()->after('filing_steps');
            $table->unsignedSmallInteger('response_deadline_days')->nullable()->after('filing_entity');
        });
    }

    public function down(): void
    {
        Schema::table('lawyers', function (Blueprint $table) {
            $table->dropColumn(['last_placement_at', 'placements_count']);
        });

        Schema::table('legal_documents', function (Blueprint $table) {
            $table->dropColumn(['filing_steps', 'filing_entity', 'response_deadline_days']);
        });
    }
};
