<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Ajustes que el equipo comercial cambia sin tocar código ni .env: logo,
 * colores, textos de los ganchos, interruptores de promoción.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('site_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('group')->default('general')->index();
            $table->longText('value')->nullable();
            $table->string('type')->default('string')->comment('string | text | bool | int | json | image');
            $table->string('label');
            $table->text('help')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('site_settings');
    }
};
