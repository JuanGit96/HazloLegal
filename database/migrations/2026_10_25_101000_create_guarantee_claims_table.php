<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('guarantee_claims', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique();
            $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
            $table->string('order_reference')->nullable()->comment('Referencia escrita por el cliente si no la encontramos');

            $table->string('client_name');
            $table->string('client_email');
            $table->string('reason')->index();
            $table->text('description');

            $table->string('status')->default('open')->index();
            $table->text('resolution_note')->nullable();
            $table->boolean('refund_issued')->default(false);
            $table->timestamp('resolved_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('guarantee_claims');
    }
};
