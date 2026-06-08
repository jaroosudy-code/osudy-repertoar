<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SetlistSong extends Model
{
    protected $fillable = ['setlist_id', 'round_id', 'song_id', 'order_position'];

    public function setlist()
    {
        return $this->belongsTo(Setlist::class);
    }

    public function round()
    {
        return $this->belongsTo(Round::class);
    }

    public function song()
    {
        return $this->belongsTo(Song::class);
    }
}
