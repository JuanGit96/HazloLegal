<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Peticiones de firma electrónica.
 *
 * La Ley 527 de 1999 no exige un formato concreto de firma, pero sí que se
 * pueda identificar al firmante y comprobar que el documento no cambió
 * después. De ahí que aquí se guarde el rastro completo —cuándo se abrió,
 * desde qué IP, con qué navegador y el hash del documento firmado— y no
 * solo la imagen del trazo.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('signature_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();

            // El token es la credencial del enlace que se abre en el celular.
            $table->string('token', 64)->unique();

            $table->string('signer_name');
            $table->string('signer_document')->nullable();

            $table->string('status')->default('pending')->index()
                ->comment('pending | signed | expired');

            $table->longText('signature_data')->nullable()->comment('Trazo en SVG');
            $table->string('signature_path')->nullable()->comment('PNG del trazo');

            // La evidencia de la firma.
            $table->timestamp('opened_at')->nullable();
            $table->timestamp('signed_at')->nullable();
            $table->string('signed_ip')->nullable();
            $table->string('signed_user_agent')->nullable();
            $table->string('document_hash', 64)->nullable()->comment('SHA-256 del PDF firmado');
            $table->string('otp_code', 10)->nullable();
            $table->timestamp('otp_sent_at')->nullable();

            $table->timestamp('expires_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('signature_requests');
    }
};
