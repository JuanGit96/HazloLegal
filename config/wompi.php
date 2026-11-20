<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Wompi
|--------------------------------------------------------------------------
| Pasarela de pagos colombiana. Se usa el Checkout Web: el cliente sale a
| pagar al dominio de Wompi y vuelve. Aquí nunca entra un número de tarjeta,
| que es justo lo que no queremos tener que custodiar.
|
| Las llaves van por .env. Las de prueba empiezan por pub_test_ / prv_test_.
*/

return [

    'enabled' => env('WOMPI_ENABLED', true),

    // sandbox | production. En sandbox el dominio y las llaves son de prueba
    // y ningún cobro es real.
    'mode' => env('WOMPI_MODE', 'sandbox'),

    'public_key' => env('WOMPI_PUBLIC_KEY'),
    'private_key' => env('WOMPI_PRIVATE_KEY'),

    // Firma de integridad: se concatena referencia + monto + moneda + secreto
    // y se manda el SHA-256. Es lo que impide que alguien cambie el precio en
    // el enlace antes de pagar.
    'integrity_secret' => env('WOMPI_INTEGRITY_SECRET'),

    // Secreto de los eventos: valida que el webhook viene de Wompi y no de
    // cualquiera que conozca la URL.
    'events_secret' => env('WOMPI_EVENTS_SECRET'),

    'urls' => [
        // El checkout es el mismo dominio en los dos entornos: lo que decide
        // si el cobro es real es la llave pública. La API sí cambia, y
        // apuntarla al entorno equivocado devuelve 401 sin decir por qué.
        'sandbox' => [
            'api' => env('WOMPI_API_URL', 'https://sandbox.wompi.co/v1'),
            'checkout' => 'https://checkout.wompi.co/p/',
        ],
        'production' => [
            'api' => env('WOMPI_API_URL', 'https://production.wompi.co/v1'),
            'checkout' => 'https://checkout.wompi.co/p/',
        ],
    ],

    'currency' => 'COP',

    // Métodos que se le ofrecen al cliente en el checkout de Wompi.
    'payment_methods' => ['CARD', 'NEQUI', 'PSE', 'BANCOLOMBIA_TRANSFER'],
];
