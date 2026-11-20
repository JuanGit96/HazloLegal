<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * El cuerpo del documento y la llave para descargarlo.
 *
 * `template_body` es lo que se imprime en el PDF del cliente, con marcadores
 * del tipo {{ cliente.nombre }}; `template_fields` son las preguntas que el
 * trámite necesita, editables desde el panel sin tocar código.
 *
 * `access_token` existe porque la referencia (HL-2026-00042) es correlativa y
 * por tanto adivinable: sin una llave aparte, cualquiera podría recorrer los
 * pedidos y leer datos personales ajenos.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('legal_documents', function (Blueprint $table) {
            $table->longText('template_body')->nullable()->after('body')
                ->comment('Cuerpo del documento con marcadores {{ cliente.nombre }}');
            $table->json('template_fields')->nullable()->after('template_body')
                ->comment('Campos del formulario, editables desde el panel');
            $table->string('template_heading')->nullable()->after('template_fields')
                ->comment('Encabezado del PDF: SEÑORES…');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->string('access_token', 64)->nullable()->unique()->after('reference');
        });
    }

    public function down(): void
    {
        Schema::table('legal_documents', function (Blueprint $table) {
            $table->dropColumn(['template_body', 'template_fields', 'template_heading']);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('access_token');
        });
    }
};
