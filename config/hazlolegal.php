<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Configuración de negocio de Hazlo Legal
|--------------------------------------------------------------------------
| Todo lo que el equipo comercial cambia sin tocar código vive aquí o en
| el panel. Lo que cambia por entorno (números, cuentas, IDs) va por .env.
*/

return [

    'brand' => [
        'name' => 'Hazlo Legal',
        'legal_name' => env('HL_LEGAL_NAME', 'Hazlo Legal S.A.S.'),
        'nit' => env('HL_NIT', '901.000.000-1'),
        'tagline' => 'Certeza legal, sin honorarios sorpresa.',
        'founded' => '2026',
    ],

    'contact' => [
        // Número en formato internacional sin "+" ni espacios (para wa.me).
        'whatsapp' => env('HL_WHATSAPP', '573001234567'),
        'whatsapp_display' => env('HL_WHATSAPP_DISPLAY', '+57 300 123 4567'),
        'email' => env('HL_EMAIL', 'hola@hazlolegal.co'),
        'cases_email' => env('HL_CASES_EMAIL', 'casos@hazlolegal.co'),
        'phone' => env('HL_PHONE', '+57 601 123 4567'),
        'address' => env('HL_ADDRESS', 'Calle 100 # 19-61, Oficina 802'),
        'city' => 'Bogotá',
        'region' => 'Cundinamarca',
        'country' => 'CO',
        'postal_code' => '110111',
        'latitude' => '4.6838',
        'longitude' => '-74.0484',
        'hours' => 'Lunes a sábado, 7:00 a.m. a 9:00 p.m.',
        'hours_schema' => 'Mo-Sa 07:00-21:00',
    ],

    'social' => [
        'facebook' => env('HL_FACEBOOK', 'https://facebook.com/hazlolegal'),
        'instagram' => env('HL_INSTAGRAM', 'https://instagram.com/hazlolegal'),
        'linkedin' => env('HL_LINKEDIN', 'https://linkedin.com/company/hazlolegal'),
        'tiktok' => env('HL_TIKTOK', 'https://tiktok.com/@hazlolegal'),
        'youtube' => env('HL_YOUTUBE', 'https://youtube.com/@hazlolegal'),
    ],

    /*
    | Certificado de Garantía: la promesa que sostiene la conversión.
    | Cambiar estos valores cambia la landing, el PDF y los correos.
    */
    'guarantee' => [
        'version' => '1.0',
        'claim_window_days' => 30,
        'correction_hours' => 24,
        'unlimited_corrections' => true,
        'refund_percentage' => 100,
    ],

    /*
    | Reparto de casos complejos: se envía a varios abogados y se lo queda
    | el primero que acepta.
    */
    'cases' => [
        'max_lawyers_notified' => (int) env('HL_CASE_MAX_LAWYERS', 8),
        'offer_ttl_hours' => (int) env('HL_CASE_OFFER_TTL', 48),
        'notify_fallback_all_areas' => true,
    ],

    'payments' => [
        'nequi' => env('HL_NEQUI', '300 123 4567'),
        'daviplata' => env('HL_DAVIPLATA', '300 123 4567'),
        'bank' => env('HL_BANK', 'Bancolombia'),
        'bank_account' => env('HL_BANK_ACCOUNT', 'Ahorros 123-456789-00'),
        // En dummy no se cobra: el checkout sólo registra el pedido.
        'gateway_enabled' => (bool) env('HL_GATEWAY_ENABLED', false),
    ],

    'seo' => [
        'default_title' => 'Hazlo Legal | Documentos y trámites legales en Colombia, listos en 24 horas',
        'default_description' => 'Tutelas, derechos de petición y contratos redactados por abogados colombianos titulados. Entrega en 24 horas o 4 horas express, con garantía de corrección por escrito.',
        'og_image' => '/img/og-hazlolegal.png',
        'locale' => 'es_CO',
        'twitter_handle' => '@hazlolegal',
        'google_site_verification' => env('HL_GOOGLE_VERIFICATION'),
        'ga_measurement_id' => env('HL_GA_ID'),
        'meta_pixel_id' => env('HL_META_PIXEL'),
    ],

    'trust' => [
        'documents_delivered' => (int) env('HL_STAT_DOCS', 1840),
        'avg_delivery_hours' => 19,
        'lawyer_count' => (int) env('HL_STAT_LAWYERS', 14),
        'satisfaction' => '4.9',
    ],
];
