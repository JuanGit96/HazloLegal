<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * El fundamento común y el cierre del escrito.
 *
 * Van en el trámite y no en cada casilla porque se escriben una vez y
 * aparecen siempre, se marque lo que se marque.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('legal_documents', function (Blueprint $table) {
            $table->text('clause_common_basis')->nullable()->after('template_heading')
                ->comment('Normas que cita el escrito en todos los casos');
            $table->text('clause_closing')->nullable()->after('clause_common_basis')
                ->comment('Párrafo de cierre, siempre el mismo');
        });
    }

    public function down(): void
    {
        Schema::table('legal_documents', function (Blueprint $table) {
            $table->dropColumn(['clause_common_basis', 'clause_closing']);
        });
    }
};
