<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * El dato del lead es parte del producto que se le vende al abogado, así que
 * el consentimiento tiene que ser explícito, granular y auditable.
 *
 * Ley 1581 de 2012: la autorización debe ser previa, expresa e informada, y
 * el titular puede revocarla. Por eso se guarda QUÉ aceptó, CUÁNDO, desde
 * qué IP y con qué versión de la política.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->foreignId('order_id')->nullable()->after('post_id')
                ->constrained()->nullOnDelete();
            $table->foreignId('calculator_id')->nullable()->after('order_id')
                ->constrained()->nullOnDelete();

            $table->string('practice_area')->nullable()->after('source')->index();
            $table->string('city')->nullable()->after('practice_area');
            $table->text('case_summary')->nullable()->after('city');

            // Consentimientos separados: uno no implica el otro.
            $table->boolean('consent_marketing')->default(false)->after('consented_at');
            $table->boolean('consent_share_with_lawyers')->default(false)->after('consent_marketing');
            $table->string('consent_policy_version')->nullable()->after('consent_share_with_lawyers');
            $table->timestamp('consent_revoked_at')->nullable()->after('consent_policy_version');

            $table->string('quality')->default('cold')->after('is_subscribed')
                ->comment('cold | warm | hot — según lo que hizo en el sitio');
            $table->timestamp('last_activity_at')->nullable()->after('quality');
        });

        // Trazabilidad de quién vio qué lead: exigible si la SIC pregunta.
        Schema::create('lead_accesses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lead_id')->constrained()->cascadeOnDelete();
            $table->foreignId('lawyer_id')->constrained()->cascadeOnDelete();
            $table->string('action')->default('viewed')->comment('viewed | contacted | discarded');
            $table->timestamp('accessed_at');
            $table->string('ip_address')->nullable();
            $table->timestamps();

            $table->index(['lawyer_id', 'accessed_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lead_accesses');

        Schema::table('leads', function (Blueprint $table) {
            $table->dropConstrainedForeignId('order_id');
            $table->dropConstrainedForeignId('calculator_id');
            $table->dropColumn([
                'practice_area', 'city', 'case_summary',
                'consent_marketing', 'consent_share_with_lawyers',
                'consent_policy_version', 'consent_revoked_at',
                'quality', 'last_activity_at',
            ]);
        });
    }
};
