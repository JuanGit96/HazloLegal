<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Bajarse de plan y cancelar, sin perder lo ya pagado.
 *
 * Subir cambia el plan hoy, porque hoy se cobra la diferencia. Bajar y
 * cancelar no: el periodo ya está pagado y quitárselo antes de tiempo sería
 * cobrar por algo que no se entrega. Así que la decisión se guarda y se
 * aplica el día de la renovación.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lawyer_subscriptions', function (Blueprint $table) {
            // Sin clave foránea a propósito: añadirla a una tabla que ya
            // existe obliga a SQLite a reconstruirla, y las pruebas corren
            // sobre SQLite. El plan se resuelve por relación igual.
            $table->unsignedBigInteger('scheduled_plan_id')->nullable()->after('lawyer_plan_id')
                ->comment('El plan que entra a regir en la próxima renovación');

            $table->timestamp('ends_at')->nullable()->after('renews_at')
                ->comment('Hasta cuándo vale lo pagado. Después no renueva.');

            $table->string('cancellation_reason')->nullable()->after('cancelled_at')
                ->comment('Por qué se va. Es lo único que explica una baja.');

            $table->index('scheduled_plan_id');
        });
    }

    public function down(): void
    {
        Schema::table('lawyer_subscriptions', function (Blueprint $table) {
            $table->dropIndex(['scheduled_plan_id']);
            $table->dropColumn(['scheduled_plan_id', 'ends_at', 'cancellation_reason']);
        });
    }
};
