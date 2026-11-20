<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->unsignedInteger('base_amount')->default(0)->after('amount');
            $table->unsignedInteger('addons_amount')->default(0)->after('base_amount');
            $table->unsignedInteger('discount_amount')->default(0)->after('addons_amount');

            $table->string('tier')->default('free')->after('status')
                ->index()->comment('free | self_service | accompanied');

            // El documento generado y su instructivo viven separados: el
            // instructivo es además la pieza publicitaria que circula.
            $table->string('document_path')->nullable()->after('delivered_file_path');
            $table->string('instructions_path')->nullable()->after('document_path');
            $table->foreignId('featured_lawyer_id')->nullable()->after('assigned_lawyer_id')
                ->constrained('lawyers')->nullOnDelete()
                ->comment('Abogado patrocinado que aparece en el instructivo');

            $table->boolean('accepts_marketing')->default(false)->after('wants_signature');
            $table->boolean('shareable_with_lawyers')->default(false)->after('accepts_marketing')
                ->comment('El titular autorizó compartir el caso con abogados de la red');

            $table->string('signature_path')->nullable()->after('instructions_path');
            $table->timestamp('signed_at')->nullable()->after('signature_path');

            $table->json('ai_review')->nullable()->after('answers')
                ->comment('Resultado de la revisión automática de redacción');
        });

        // El plan deja de ser obligatorio: ahora el precio nace del documento.
        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('service_plan_id')->nullable()->change();
        });

        Schema::create('order_addons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('service_addon_id')->nullable()->constrained()->nullOnDelete();

            // Congelamos nombre y precio: si mañana sube el adicional, el
            // pedido viejo tiene que seguir contando lo que se cobró.
            $table->string('key');
            $table->string('name');
            $table->unsignedInteger('price');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_addons');

        Schema::table('orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('featured_lawyer_id');
            $table->dropColumn([
                'base_amount', 'addons_amount', 'discount_amount', 'tier',
                'document_path', 'instructions_path', 'accepts_marketing',
                'shareable_with_lawyers', 'signature_path', 'signed_at', 'ai_review',
            ]);
        });
    }
};
