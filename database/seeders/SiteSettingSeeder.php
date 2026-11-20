<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Domain\Settings\Models\SiteSetting;
use Illuminate\Database\Seeder;

class SiteSettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            // ---------------------------------------------- Marca
            ['key' => 'brand_name', 'group' => 'marca', 'type' => 'string', 'label' => 'Nombre de la marca', 'value' => 'HazloLegal', 'sort_order' => 1],
            ['key' => 'brand_tagline', 'group' => 'marca', 'type' => 'string', 'label' => 'Lema', 'value' => 'Documentos legales sin vacíos, sin letra menuda.', 'sort_order' => 2],
            ['key' => 'brand_logo', 'group' => 'marca', 'type' => 'image', 'label' => 'Logo principal', 'help' => 'SVG o PNG con fondo transparente. Se usa en el sitio, el panel y los PDF.', 'sort_order' => 3],
            ['key' => 'brand_logo_dark', 'group' => 'marca', 'type' => 'image', 'label' => 'Logo para fondos oscuros', 'sort_order' => 4],
            ['key' => 'brand_favicon', 'group' => 'marca', 'type' => 'image', 'label' => 'Favicon', 'sort_order' => 5],
            ['key' => 'brand_color_primary', 'group' => 'marca', 'type' => 'string', 'label' => 'Color primario', 'value' => '#1D4ED8', 'sort_order' => 6],
            ['key' => 'brand_color_accent', 'group' => 'marca', 'type' => 'string', 'label' => 'Color de acento', 'value' => '#F97316', 'sort_order' => 7],

            // ---------------------------------------------- Ganchos comerciales
            ['key' => 'hook_headline', 'group' => 'ganchos', 'type' => 'string', 'label' => 'Titular de la home', 'value' => 'Tu derecho de petición, ==gratis== y sin vacíos legales', 'help' => 'Lo que se escriba entre ==dobles iguales== sale con el marcador de la marca. Máximo dos por titular: si se resalta todo, no resalta nada.', 'sort_order' => 1],
            ['key' => 'hook_subheadline', 'group' => 'ganchos', 'type' => 'text', 'label' => 'Subtítulo de la home', 'value' => 'Lo generas en 3 minutos, revisamos la redacción y te lo llevas listo para radicar. Sin registro, sin costo.', 'sort_order' => 2],
            ['key' => 'hook_ai_enabled', 'group' => 'ganchos', 'type' => 'bool', 'label' => 'Mostrar la franja de revisión de redacción', 'value' => '1', 'help' => 'Dice que corregimos ortografía, tono y puntuación. No prometas revisión con IA mientras el corrector sea de reglas: es una afirmación que no podríamos sostener.', 'sort_order' => 3],
            ['key' => 'hook_free_count', 'group' => 'ganchos', 'type' => 'string', 'label' => 'Documentos generados (prueba social)', 'value' => '12.400', 'help' => 'Debe reflejar el número real. Un dato inflado es publicidad engañosa.', 'sort_order' => 4],

            // ---------------------------------------------- Promociones
            ['key' => 'promo_enabled', 'group' => 'promociones', 'type' => 'bool', 'label' => 'Mostrar precios tachados', 'value' => '1', 'help' => 'Solo debe activarse si el precio tachado fue un precio real.', 'sort_order' => 1],
            ['key' => 'promo_banner_enabled', 'group' => 'promociones', 'type' => 'bool', 'label' => 'Mostrar franja de promoción', 'value' => '0', 'sort_order' => 2],
            ['key' => 'promo_banner_text', 'group' => 'promociones', 'type' => 'string', 'label' => 'Texto de la franja', 'value' => '', 'sort_order' => 3],
            ['key' => 'promo_upsell_enabled', 'group' => 'promociones', 'type' => 'bool', 'label' => 'Ofrecer adicionales tras elegir', 'value' => '1', 'sort_order' => 4],

            // ---------------------------------------------- Operación
            ['key' => 'default_delivery_hours', 'group' => 'operacion', 'type' => 'int', 'label' => 'Horas de entrega por defecto', 'value' => '24', 'sort_order' => 1],
            ['key' => 'free_docs_enabled', 'group' => 'operacion', 'type' => 'bool', 'label' => 'Documentos gratuitos activos', 'value' => '1', 'sort_order' => 2],
            ['key' => 'calculators_enabled', 'group' => 'operacion', 'type' => 'bool', 'label' => 'Calculadoras activas', 'value' => '1', 'sort_order' => 3],
            ['key' => 'lawyer_directory_enabled', 'group' => 'operacion', 'type' => 'bool', 'label' => 'Directorio de abogados activo', 'value' => '1', 'sort_order' => 4],
        ];

        foreach ($settings as $setting) {
            SiteSetting::query()->updateOrCreate(['key' => $setting['key']], $setting);
        }
    }
}
