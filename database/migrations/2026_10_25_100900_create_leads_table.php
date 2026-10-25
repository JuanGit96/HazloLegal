<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->string('email')->index();
            $table->string('name')->nullable();
            $table->string('phone')->nullable();
            $table->string('source')->default('newsletter')->comment('newsletter | lead_magnet | exit_intent');
            $table->foreignId('legal_document_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('post_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamp('consented_at')->nullable();
            $table->string('ip_address')->nullable();
            $table->boolean('is_subscribed')->default(true);
            $table->timestamps();

            $table->unique(['email', 'source']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};
