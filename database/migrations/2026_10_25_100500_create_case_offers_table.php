<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('case_offers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('legal_case_id')->constrained()->cascadeOnDelete();
            $table->foreignId('lawyer_id')->constrained()->cascadeOnDelete();

            // Token firmado en el correo: permite aceptar sin iniciar sesión.
            $table->string('token', 64)->unique();

            $table->string('status')->default('pending')->index();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('responded_at')->nullable();
            $table->text('decline_reason')->nullable();
            $table->string('response_ip')->nullable();

            $table->timestamps();

            $table->unique(['legal_case_id', 'lawyer_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('case_offers');
    }
};
