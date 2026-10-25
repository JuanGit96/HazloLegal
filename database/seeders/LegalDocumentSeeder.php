<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Domain\Catalog\Models\LegalDocument;
use Illuminate\Database\Seeder;

class LegalDocumentSeeder extends Seeder
{
    public function run(): void
    {
        $documents = [
            ...require database_path('seeders/data/documents-phase2.php'),
            ...require database_path('seeders/data/documents-phase1.php'),
        ];

        foreach ($documents as $document) {
            LegalDocument::query()->updateOrCreate(
                ['slug' => $document['slug']],
                [
                    // El precio base vive en el plan; el modificador queda en 0
                    // y se ajusta desde el panel si un trámite pesa más.
                    'price_modifier' => 0,
                    'available_express' => true,
                    'requires_lawyer_review' => true,
                    'is_active' => true,
                    ...$document,
                ],
            );
        }
    }
}
