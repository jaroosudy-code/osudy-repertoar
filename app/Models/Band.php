<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Band extends Model
{
    protected $fillable = ['name', 'slug', 'description'];

    public function users()
    {
        return $this->belongsToMany(User::class)
            ->withPivot(['permissions', 'is_band_admin'])
            ->withTimestamps();
    }

    public function songs()
    {
        return $this->belongsToMany(Song::class, 'band_song')
            ->withPivot(['added_by_user_id'])
            ->withTimestamps();
    }

    public function setlists()
    {
        return $this->hasMany(Setlist::class);
    }
}
