<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * El instructivo deja de ser genérico.
 *
 * Es lo que se cobra, así que tiene que decirle a alguien que nunca ha
 * radicado nada exactamente qué hacer: a dónde ir, qué llevar, qué pedir al
 * entregarlo, cuánto esperar y qué hacer si no le responden. Un texto
 * igual para los nueve trámites no puede hacer eso.
 *
 * Las advertencias del semáforo viven aquí y no en el checkout: avisarle al
 * usuario cuando ya tiene el documento en la mano, con la hoja que se va a
 * llevar a la ventanilla, le sirve más que una alerta que cerró de paso.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('legal_documents', function (Blueprint $table) {
            $table->json('filing_channels')->nullable()->after('filing_entity')
                ->comment('Dónde se radica: nombre, enlace y fecha de verificación');

            $table->text('instructions_intro')->nullable()->after('filing_channels')
                ->comment('Qué es este documento, en una frase que entienda cualquiera');

            $table->text('instructions_footer')->nullable()->after('instructions_intro')
                ->comment('Frase fija del pie del instructivo');

            $table->json('instructions_warnings')->nullable()->after('instructions_footer')
                ->comment('Cuándo este documento no alcanza y qué hacer entonces');

            $table->json('instructions_attachments')->nullable()->after('instructions_warnings')
                ->comment('Qué llevar o adjuntar al radicar');

            $table->json('glossary')->nullable()->after('instructions_attachments')
                ->comment('Las palabras raras del trámite, explicadas');
        });
    }

    public function down(): void
    {
        Schema::table('legal_documents', function (Blueprint $table) {
            $table->dropColumn([
                'filing_channels', 'instructions_intro', 'instructions_footer',
                'instructions_warnings', 'instructions_attachments', 'glossary',
            ]);
        });
    }
};
