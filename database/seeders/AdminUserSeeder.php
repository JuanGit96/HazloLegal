<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Domain\Lawyers\Models\Lawyer;
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
                'name' => 'Equipo HazloLegal',
                'password' => Hash::make('hazlolegal2026'),
                'email_verified_at' => now(),
                'is_active' => true,
                'is_staff' => true,
            ],
        );

        // Un acceso de abogado para poder recorrer el portal en la demo. No
        // es del equipo: entra a /portal y no ve nada que no sea suyo.
        $lawyer = Lawyer::query()->where('email', 'daniela.restrepo@hazlolegal.co')->first();

        if ($lawyer) {
            $user = User::query()->updateOrCreate(
                ['email' => $lawyer->email],
                [
                    'name' => $lawyer->name,
                    'password' => Hash::make('abogado2026'),
                    'email_verified_at' => now(),
                    'is_active' => true,
                    'is_staff' => false,
                ],
            );

            $lawyer->forceFill(['user_id' => $user->id])->saveQuietly();
        }
    }
}
