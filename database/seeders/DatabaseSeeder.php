<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            SiteSettingSeeder::class,
            ServiceAddonSeeder::class,
            LawyerSeeder::class,
            // Después de los abogados: el acceso de demostración al portal
            // se engancha a uno de ellos.
            AdminUserSeeder::class,
            LegalDocumentSeeder::class,
            PricingSeeder::class,
            ProductSeeder::class,
            LawyerPlanSeeder::class,
            // Después de los planes: una promoción puede estar atada a uno.
            SubscriptionPromoSeeder::class,
            CalculatorSeeder::class,
            FaqSeeder::class,
            TestimonialSeeder::class,
            BlogSeeder::class,
            DemoActivitySeeder::class,
            DemoSubscriptionSeeder::class,
            // Cifras inventadas para poder mirar el portal del abogado antes
            // de que haya tráfico real. No se ejecuta en producción.
            DemoMetricsSeeder::class,
        ]);
    }
}
