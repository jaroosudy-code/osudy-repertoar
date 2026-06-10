<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setlist extends Model
{
    protected $fillable = ['name', 'event_type', 'event_date', 'notes', 'user_id'];

    protected $casts = ['event_date' => 'date'];

    public function rounds()
    {
        return $this->hasMany(Round::class)->orderBy('order_position');
    }

    public function setlistSongs()
    {
        return $this->hasMany(SetlistSong::class)->orderBy('order_position');
    }

    public function concertSongs()
    {
        return $this->hasMany(SetlistSong::class)->whereNull('round_id')->orderBy('order_position');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function canBeEditedBy(User $user): bool
    {
        if ($user->isAdmin()) return true;
        if ($user->hasPermission('setlists.edit_all')) return true;
        return $this->user_id === $user->id && $user->hasPermission('setlists.edit_own');
    }

    public function canBeDeletedBy(User $user): bool
    {
        if ($user->isAdmin()) return true;
        if ($user->hasPermission('setlists.delete_all')) return true;
        return $this->user_id === $user->id && $user->hasPermission('setlists.delete_own');
    }
}
