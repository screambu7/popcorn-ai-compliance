<?php

namespace App\Models;

use App\Notifications\ResetPasswordNotification;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'role', 'is_active'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    const ROLES = [
        'admin'                => 'Administrador',
        'oficial_cumplimiento' => 'Oficial de Cumplimiento',
        'operador'             => 'Operador',
        'auditor'              => 'Auditor',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new ResetPasswordNotification($token));
    }

    // -------------------------------------------------------------------------
    // Role accessors
    // -------------------------------------------------------------------------

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isOficialCumplimiento(): bool
    {
        return $this->role === 'oficial_cumplimiento';
    }

    public function isOperador(): bool
    {
        return $this->role === 'operador';
    }

    public function isAuditor(): bool
    {
        return $this->role === 'auditor';
    }

    /**
     * Verifica si el usuario tiene uno de los roles especificados.
     */
    public function hasRole(string ...$roles): bool
    {
        return in_array($this->role, $roles);
    }

    // -------------------------------------------------------------------------
    // Scopes
    // -------------------------------------------------------------------------

    public function scopeByRole($query, string $role)
    {
        return $query->where('role', $role);
    }

    public function scopeActivos($query)
    {
        return $query->where('is_active', true);
    }
}
