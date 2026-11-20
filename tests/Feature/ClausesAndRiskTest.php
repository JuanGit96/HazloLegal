<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domain\Catalog\Enums\RiskLevel;
use App\Domain\Catalog\Models\LegalDocument;
use App\Domain\Catalog\Services\ClauseComposer;
use App\Domain\Catalog\Services\RiskAssessor;
use Database\Seeders\ProductSeeder;
use Database\Seeders\LegalDocumentSeeder;
use Database\Seeders\PricingSeeder;
use Database\Seeders\ServiceAddonSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClausesAndRiskTest extends TestCase
{
    use RefreshDatabase;

    private LegalDocument $document;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            ServiceAddonSeeder::class,
            LegalDocumentSeeder::class,
            PricingSeeder::class,
            ProductSeeder::class,
        ]);

        $this->document = LegalDocument::query()->where('slug', 'derecho-de-peticion')->firstOrFail();
    }

    public function test_el_escrito_se_arma_con_las_casillas_marcadas(): void
    {
        $resultado = app(ClauseComposer::class)->compose(
            $this->document,
            ['medicamentos', 'cita_especialista'],
            [
                'medicamentos' => ['medicamentos' => 'Losartán 50 mg', 'fecha de la fórmula' => '12 de enero de 2026'],
                'cita_especialista' => ['especialidad' => 'cardiología', 'fecha de la orden' => '15 de enero de 2026'],
            ],
        );

        $this->assertCount(2, $resultado['peticiones']);
        $this->assertStringContainsString('Losartán 50 mg', $resultado['peticiones'][0]);
        $this->assertStringContainsString('cardiología', $resultado['peticiones'][1]);
        $this->assertSame([], $resultado['faltantes']);
    }

    public function test_el_fundamento_no_repite_normas(): void
    {
        // Las cuatro primeras casillas comparten la Ley 1751: un escrito que
        // la cita cuatro veces se lee como plantilla mal armada.
        $resultado = app(ClauseComposer::class)->compose(
            $this->document,
            ['procedimiento', 'medicamentos', 'cita_especialista', 'transporte'],
        );

        $this->assertSame(1, substr_count($resultado['fundamento'], 'Ley 1751 de 2015'));
        $this->assertStringContainsString('Artículo 23 de la Constitución', $resultado['fundamento']);
    }

    public function test_pedir_la_historia_clinica_acorta_el_plazo(): void
    {
        $sola = app(ClauseComposer::class)->compose($this->document, ['historia_clinica']);
        $this->assertSame(10, $sola['plazo_dias']);

        // Junto a otra petición vuelve a regir el plazo general.
        $acompanada = app(ClauseComposer::class)->compose($this->document, ['historia_clinica', 'medicamentos']);
        $this->assertSame(15, $acompanada['plazo_dias']);
    }

    public function test_avisa_de_las_variables_sin_contestar(): void
    {
        $resultado = app(ClauseComposer::class)->compose($this->document, ['medicamentos']);

        $this->assertCount(2, $resultado['faltantes']);
        $this->assertStringContainsString('[falta:', $resultado['peticiones'][0]);
    }

    public function test_un_caso_sin_senales_es_verde(): void
    {
        $riesgo = app(RiskAssessor::class)->assess(
            $this->document,
            [],
            'Pedí una cita de control hace tres semanas y no me la han asignado.',
        );

        $this->assertSame(RiskLevel::Green, $riesgo->level);
        $this->assertTrue($riesgo->allowsDocument());
    }

    public function test_estar_hospitalizado_pone_el_caso_en_rojo(): void
    {
        // Quien está en urgencias no necesita un derecho de petición mejor
        // redactado: necesita una tutela.
        $riesgo = app(RiskAssessor::class)->assess($this->document, ['hospitalizado' => '1']);

        $this->assertSame(RiskLevel::Red, $riesgo->level);
        $this->assertFalse($riesgo->allowsDocument());
    }

    public function test_una_sola_senal_ambar_deja_el_caso_en_ambar(): void
    {
        $riesgo = app(RiskAssessor::class)->assess($this->document, ['embarazo' => '1']);

        $this->assertSame(RiskLevel::Amber, $riesgo->level);
        $this->assertTrue($riesgo->allowsDocument());
        $this->assertTrue($riesgo->requiresAcknowledgement());
    }

    public function test_dos_senales_ambares_suben_a_rojo(): void
    {
        $riesgo = app(RiskAssessor::class)->assess($this->document, [
            'embarazo' => '1',
            'ya_pidio' => '1',
        ]);

        $this->assertSame(RiskLevel::Red, $riesgo->level);
    }

    public function test_una_palabra_en_el_texto_no_bloquea_sola(): void
    {
        // La palabra dispara la señal, pero la decisión la confirma el
        // usuario en la pregunta: decidir por una palabra suelta daría
        // falsos positivos.
        $riesgo = app(RiskAssessor::class)->assess(
            $this->document,
            [],
            'Me negaron la quimioterapia que ordenó el oncólogo.',
        );

        $this->assertSame(RiskLevel::Amber, $riesgo->level);
        $this->assertTrue($riesgo->allowsDocument());
    }

    public function test_las_tildes_no_despistan_al_semaforo(): void
    {
        $riesgo = app(RiskAssessor::class)->assess($this->document, [], 'Tengo cancer de tiroides.');

        $this->assertSame(RiskLevel::Amber, $riesgo->level);
    }
}
