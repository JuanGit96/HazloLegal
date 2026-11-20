<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Separa a quien opera el negocio de quien solo entra a lo suyo.
 *
 * Desde que los abogados tienen su propio portal hay dos clases de usuario
 * en la misma tabla. Sin esta marca, un abogado suscrito entraría al panel
 * de administración y vería los pedidos, los ingresos y los datos de los
 * clientes de los demás.
 *
 * Las filas que ya existen son del equipo, así que entran con true.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_staff')->default(false)->after('is_active')
                ->comment('Acceso al panel de administración');
        });

        \Illuminate\Support\Facades\DB::table('users')->update(['is_staff' => true]);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('is_staff');
        });
    }
};
