<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * El cliente elige a su abogado.
 *
 * Antes ganaba el primero que aceptaba. Premiaba la velocidad de respuesta,
 * no lo que al cliente le conviene: alguien con un problema de familia
 * prefiere escoger entre tres perfiles a que le toque el que estaba mirando
 * el correo.
 *
 * Ahora los abogados se postulan y el cliente decide. `client_token` existe
 * porque la referencia (CC-2026-00001) es correlativa y adivinable: sin una
 * llave aparte, cualquiera podría abrir la lista de postulados de otro y ver
 * su caso.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('legal_cases', function (Blueprint $table) {
            $table->string('client_token', 64)->nullable()->unique()->after('reference');
            $table->timestamp('shortlisted_at')->nullable()->after('dispatched_at')
                ->comment('Cuándo se le avisó al cliente que ya había postulados');
        });

        Schema::table('case_offers', function (Blueprint $table) {
            $table->text('pitch')->nullable()->after('decline_reason')
                ->comment('Lo que el abogado le dice al cliente para que lo elija');
        });

        // Los casos que ya existen necesitan su llave.
        foreach (\Illuminate\Support\Facades\DB::table('legal_cases')->whereNull('client_token')->pluck('id') as $id) {
            \Illuminate\Support\Facades\DB::table('legal_cases')
                ->where('id', $id)
                ->update(['client_token' => bin2hex(random_bytes(16))]);
        }
    }

    public function down(): void
    {
        Schema::table('legal_cases', function (Blueprint $table) {
            $table->dropColumn(['client_token', 'shortlisted_at']);
        });

        Schema::table('case_offers', function (Blueprint $table) {
            $table->dropColumn('pitch');
        });
    }
};
