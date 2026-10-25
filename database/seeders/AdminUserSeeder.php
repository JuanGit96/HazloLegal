<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        // Credenciales de demostración del prototipo. Cambiarlas antes de
        // exponer el panel a internet.
        User::query()->updateOrCreate(
            ['email' => 'admin@hazlolegal.co'],
            [
                'name' => 'Equipo Hazlo Legal',
                'password' => Hash::make('hazlolegal2026'),
                'email_verified_at' => now(),
            ],
        );
    }
}
