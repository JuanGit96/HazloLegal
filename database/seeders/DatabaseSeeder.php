<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            AdminUserSeeder::class,
            ServicePlanSeeder::class,
            LawyerSeeder::class,
            LegalDocumentSeeder::class,
            FaqSeeder::class,
            TestimonialSeeder::class,
            BlogSeeder::class,
            DemoActivitySeeder::class,
        ]);
    }
}
