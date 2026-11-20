<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * La red no es solo de abogados independientes: también de firmas.
 *
 * Cambia lo que se pide al registrarse (una firma tiene NIT y un
 * representante, no una tarjeta profesional propia) y cómo se presenta en
 * el directorio. Lo que no cambia es que la verificación sigue siendo
 * manual: publicar una firma sin comprobar que existe sería prestarle la
 * marca a cualquiera.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lawyers', function (Blueprint $table) {
            $table->string('kind')->default('individual')->after('slug')->index()
                ->comment('individual | firm');

            $table->string('nit')->nullable()->after('professional_card')
                ->comment('Solo firmas');

            $table->string('contact_name')->nullable()->after('nit')
                ->comment('Representante de la firma: con quién se habla');

            $table->unsignedSmallInteger('team_size')->nullable()->after('contact_name')
                ->comment('Cuántos abogados tiene la firma');
        });
    }

    public function down(): void
    {
        Schema::table('lawyers', function (Blueprint $table) {
            $table->dropColumn(['kind', 'nit', 'contact_name', 'team_size']);
        });
    }
};
