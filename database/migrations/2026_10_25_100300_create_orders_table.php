<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique();

            $table->foreignId('legal_document_id')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('service_plan_id')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('assigned_lawyer_id')->nullable()->constrained('lawyers')->nullOnDelete();

            $table->string('speed')->default('standard');
            $table->string('status')->default('awaiting_payment')->index();

            $table->string('customer_name');
            $table->string('customer_email')->index();
            $table->string('customer_phone');
            $table->string('customer_document_type')->default('CC');
            $table->string('customer_document_number')->nullable();
            $table->string('customer_city')->nullable();

            $table->text('case_summary')->nullable();
            $table->json('answers')->nullable()->comment('Respuestas del formulario de contexto');
            $table->boolean('wants_signature')->default(false);

            $table->unsignedInteger('amount');
            $table->string('currency', 3)->default('COP');

            $table->string('payment_method')->nullable();
            $table->string('payment_reference')->nullable();
            $table->timestamp('paid_at')->nullable();

            $table->timestamp('promised_at')->nullable()->comment('Fecha límite comprometida');
            $table->timestamp('delivered_at')->nullable();
            $table->string('delivered_file_path')->nullable();
            $table->text('internal_notes')->nullable();

            $table->string('utm_source')->nullable();
            $table->string('utm_medium')->nullable();
            $table->string('utm_campaign')->nullable();

            $table->timestamps();

            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
