<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'role_id', 'last_seen_at', 'is_invisible', 'last_chat_seen_at'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected function casts(): array
    {
        return [
            'email_verified_at'  => 'datetime',
            'password'           => 'hashed',
            'last_seen_at'       => 'datetime',
            'last_chat_seen_at'  => 'datetime',
            'is_invisible'       => 'boolean',
        ];
    }

    public function scopeOnline($query)
    {
        return $query->where('last_seen_at', '>=', now()->subMinutes(5));
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function setlists()
    {
        return $this->hasMany(Setlist::class);
    }

    public function bands()
    {
        return $this->belongsToMany(Band::class)
            ->withPivot(['permissions', 'is_band_admin'])
            ->withTimestamps();
    }

    public function currentBand(): ?Band
    {
        $id = session('current_band_id');
        return $id ? $this->bands()->find($id) : null;
    }

    public function isAdmin(): bool
    {
        return $this->role?->isAdmin() ?? false;
    }

    public function hasPermission(string $permission): bool
    {
        if ($this->isAdmin()) return true;

        $bandId = session('current_band_id');
        if (! $bandId) return false;

        $band = $this->bands()->find($bandId);
        if (! $band) return false;
        if ($band->pivot->is_band_admin) return true;

        $perms = $band->pivot->permissions;
        if (is_string($perms)) {
            $perms = json_decode($perms, true) ?? [];
        }
        return in_array($permission, $perms ?? []);
    }

    public function isOnline(): bool
    {
        return $this->last_seen_at && $this->last_seen_at->gte(now()->subMinutes(5));
    }

    public function messages()
    {
        return $this->hasMany(Message::class);
    }
}
