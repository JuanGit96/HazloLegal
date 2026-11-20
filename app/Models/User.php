<?php

declare(strict_types=1);

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use App\Domain\Lawyers\Models\Lawyer;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory;

    use Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'is_active',
        'is_staff',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    /** Para que una instancia recién creada no quede con el acceso en null. */
    protected $attributes = [
        'is_active' => true,
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'is_staff' => 'boolean',
        ];
    }

    /** El abogado del directorio al que corresponde este usuario, si lo hay. */
    public function lawyer(): HasOne
    {
        return $this->hasOne(Lawyer::class);
    }

    /**
     * Quién puede entrar a qué panel.
     *
     * Son dos públicos distintos en la misma tabla: el equipo, que ve los
     * pedidos y los ingresos, y los abogados suscritos, que solo pueden ver
     * lo suyo. Filament solo salta esta comprobación en local; en producción
     * un usuario sin ella recibe 403.
     */
    public function canAccessPanel(Panel $panel): bool
    {
        if (! $this->is_active) {
            return false;
        }

        return match ($panel->getId()) {
            'lawyer' => $this->lawyer()->exists(),
            default => (bool) $this->is_staff,
        };
    }
}
