<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Lo que pasó cada día, ya sumado.
 *
 * El abogado paga por visibilidad, así que tiene derecho a verla. Un
 * contador acumulado no sirve para eso: «412 visitas» no dice si va
 * subiendo o si lleva dos meses plano, y la pregunta que se hace al renovar
 * es exactamente esa.
 *
 * Se guarda el agregado del día y no cada visita. Es más barato de
 * consultar, y sobre todo no guarda a nadie: sin IP, sin identificador y sin
 * forma de reconstruir quién vio qué. Contar sin identificar no es
 * tratamiento de datos personales, así que esto funciona también para quien
 * rechazó las cookies —que es la mayoría— y las cifras no mienten por la
 * mitad.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('daily_metrics', function (Blueprint $table) {
            $table->id();

            $table->date('date')->index();

            // Morfo con valores por defecto en vez de nulos: en MariaDB dos
            // nulos no chocan en un índice único, y eso partiría el contador
            // del sitio en una fila por visita.
            $table->string('subject_type')->default('site');
            $table->unsignedBigInteger('subject_id')->default(0);

            $table->string('metric', 40);
            $table->unsignedBigInteger('count')->default(0);

            $table->timestamps();

            $table->unique(['date', 'subject_type', 'subject_id', 'metric'], 'daily_metric_unico');
            $table->index(['subject_type', 'subject_id', 'metric'], 'daily_metric_sujeto');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_metrics');
    }
};
