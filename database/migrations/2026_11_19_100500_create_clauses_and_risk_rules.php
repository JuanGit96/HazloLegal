<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Lo que de verdad distingue a cada trámite.
 *
 * Hasta ahora un trámite era un precio, una plantilla y unos adicionales.
 * Las especificaciones de producto piden dos cosas más, y son las que hacen
 * que el documento sirva:
 *
 * - **Las casillas de petición**: lo que el usuario marca, con su texto
 *   exacto, sus variables y el fundamento legal que cada una arrastra. El
 *   escrito se arma concatenando lo marcado, sin repetir normas.
 * - **El semáforo**: preguntas estructuradas y palabras de guardia que
 *   detectan cuándo el caso se le queda corto a un documento automático.
 *   No es un adorno: es lo que impide vender un derecho de petición a quien
 *   está en urgencias y necesita una tutela.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_clauses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('legal_document_id')->constrained()->cascadeOnDelete();

            $table->string('key')->comment('Identificador estable dentro del trámite');
            $table->string('label')->comment('Lo que lee el usuario en la casilla');
            $table->text('body')->nullable()
                ->comment('Texto exacto que entra al escrito, con {variables}');
            $table->text('legal_basis')->nullable()
                ->comment('Fundamento que esta casilla añade al común');

            $table->json('variables')->nullable()
                ->comment('Las {llaves} del texto, con su pregunta y tipo');

            $table->boolean('is_free_text')->default(false)
                ->comment('La casilla de «petición libre»');
            $table->unsignedSmallInteger('max_chars')->nullable();

            // Hay casillas que cambian el plazo: pedir la historia clínica
            // son 10 días hábiles y no 15.
            $table->unsignedSmallInteger('response_deadline_days')->nullable();

            $table->boolean('needs_lawyer_review')->default(false)
                ->comment('Marcado [MAICOL]: un abogado tiene que aprobar el texto');
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);

            $table->timestamps();

            $table->unique(['legal_document_id', 'key']);
        });

        Schema::create('document_risk_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('legal_document_id')->constrained()->cascadeOnDelete();

            $table->string('kind')->index()->comment('question | keyword | blocked');
            $table->string('level')->comment('red | amber | blocked');

            $table->string('key')->nullable()->comment('Para las preguntas');
            $table->text('prompt')->comment('La pregunta, o la palabra a vigilar');

            // «Menor de 18» es ámbar solo, pero rojo si se junta con otra.
            $table->boolean('escalates_with_others')->default(false);

            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_risk_rules');
        Schema::dropIfExists('document_clauses');
    }
};
