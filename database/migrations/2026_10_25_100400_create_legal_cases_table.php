<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('legal_cases', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique();

            $table->string('client_name');
            $table->string('client_email')->index();
            $table->string('client_phone');
            $table->string('client_city')->nullable();

            $table->string('practice_area')->index();
            $table->string('title');
            $table->longText('description');
            $table->string('urgency')->default('normal')->index();
            $table->string('budget_range')->nullable();
            $table->boolean('has_documents')->default(false);
            $table->string('deadline_note')->nullable();

            $table->string('status')->default('new')->index();

            $table->foreignId('assigned_lawyer_id')->nullable()->constrained('lawyers')->nullOnDelete();
            $table->timestamp('assigned_at')->nullable();

            $table->timestamp('dispatched_at')->nullable();
            $table->unsignedSmallInteger('offers_sent')->default(0);
            $table->timestamp('expires_at')->nullable();

            $table->text('internal_notes')->nullable();
            $table->string('source')->default('web');
            $table->string('utm_source')->nullable();
            $table->string('utm_campaign')->nullable();

            $table->timestamps();

            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('legal_cases');
    }
};
