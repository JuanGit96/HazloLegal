<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Mensajes de contacto y solicitudes de eliminación de datos.
 *
 * Van en la misma tabla porque son lo mismo desde la operación —algo que
 * alguien escribió y a lo que hay que responder— y se separan por `kind`.
 * La solicitud de eliminación, además, es un derecho del titular con plazo
 * legal (Ley 1581 de 2012), así que necesita quedar registrada con fecha y
 * poder demostrarse que se atendió.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contact_messages', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique();

            $table->string('kind')->default('contact')->index()
                ->comment('contact | data_deletion');

            $table->string('name');
            $table->string('email')->index();
            $table->string('phone')->nullable();
            $table->string('subject')->nullable();
            $table->text('message');

            // Solo para las solicitudes de eliminación.
            $table->string('document_type')->nullable();
            $table->string('document_number')->nullable();
            $table->boolean('confirms_ownership')->default(false)
                ->comment('Declaró ser el titular de los datos');

            $table->string('status')->default('new')->index()
                ->comment('new | in_progress | resolved | rejected');
            $table->text('internal_notes')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->foreignId('resolved_by')->nullable()->constrained('users')->nullOnDelete();

            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();

            $table->timestamps();

            $table->index(['kind', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contact_messages');
    }
};
