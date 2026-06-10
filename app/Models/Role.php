<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $fillable = ['name', 'slug', 'permissions'];

    protected $casts = ['permissions' => 'array'];

    const ADMIN_SLUG = 'admin';

    const ALL_PERMISSIONS = [
        'songs.create',
        'songs.edit',
        'songs.delete',
        'chords.edit',
        'setlists.create',
        'setlists.edit_own',
        'setlists.delete_own',
        'setlists.edit_all',
        'setlists.delete_all',
        'colors.manage',
    ];

    const PERMISSION_LABELS = [
        'songs.create'        => 'Pridávať piesne',
        'songs.edit'          => 'Upravovať piesne',
        'songs.delete'        => 'Mazať piesne',
        'chords.edit'         => 'Upravovať akordy',
        'setlists.create'     => 'Vytvárať playlisty',
        'setlists.edit_own'   => 'Upravovať vlastné playlisty',
        'setlists.delete_own' => 'Mazať vlastné playlisty',
        'setlists.edit_all'   => 'Upravovať akékoľvek playlisty',
        'setlists.delete_all' => 'Mazať akékoľvek playlisty',
        'colors.manage'       => 'Správa farieb',
    ];

    const PERMISSION_GROUPS = [
        'Piesne'    => ['songs.create', 'songs.edit', 'songs.delete'],
        'Akordy'    => ['chords.edit'],
        'Playlisty' => ['setlists.create', 'setlists.edit_own', 'setlists.delete_own', 'setlists.edit_all', 'setlists.delete_all'],
        'Farby'     => ['colors.manage'],
    ];

    public function isAdmin(): bool
    {
        return $this->slug === self::ADMIN_SLUG;
    }

    public function hasPermission(string $permission): bool
    {
        if ($this->isAdmin()) return true;
        return in_array($permission, $this->permissions ?? []);
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function badgeClass(): string
    {
        return match($this->slug) {
            'admin'  => 'bg-red-100 text-red-700',
            'editor' => 'bg-blue-100 text-blue-700',
            'member' => 'bg-green-100 text-green-700',
            'basic'  => 'bg-slate-100 text-slate-500',
            default  => 'bg-purple-100 text-purple-700',
        };
    }
}
