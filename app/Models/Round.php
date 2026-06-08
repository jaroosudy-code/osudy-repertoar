<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Round extends Model
{
    protected $fillable = ['setlist_id', 'name', 'order_position', 'break_after_minutes'];

    public function setlist()
    {
        return $this->belongsTo(Setlist::class);
    }

    public function setlistSongs()
    {
        return $this->hasMany(SetlistSong::class)->orderBy('order_position');
    }
}
