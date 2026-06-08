<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setlist extends Model
{
    protected $fillable = ['name', 'event_type', 'event_date', 'notes'];

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
}
