<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * La prueba de la autorización de cookies.
 *
 * El Decreto 1377 de 2013 obliga al responsable a conservar prueba de la
 * autorización, y una cookie en el navegador del visitante no es prueba: la
 * borra él. Así que la decisión se guarda a los dos lados —cookie para
 * aplicarla, fila para poder demostrarla— con la fecha, el medio y la
 * versión del texto que el visitante leyó.
 *
 * Deliberadamente sin datos de identificación: un visitante anónimo se
 * identifica con el token que lleva su cookie, y con eso basta para honrar
 * una revocación o para responderle a la SIC.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cookie_consents', function (Blueprint $table) {
            $table->id();
            $table->uuid('token')->index()->comment('El mismo valor que viaja en la cookie del visitante');

            $table->boolean('analytics')->default(false);
            $table->boolean('marketing')->default(false);

            $table->string('decision')->comment('all | necessary | custom | withdrawn');
            $table->string('policy_version', 20);

            // Para demostrar cuándo y desde dónde se dio. La IP es un dato
            // personal, así que vive solo aquí y no sale a ninguna vista.
            $table->ipAddress('ip')->nullable();
            $table->string('user_agent', 400)->nullable();
            $table->string('source', 60)->nullable()->comment('banner | preferencias');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cookie_consents');
    }
};
