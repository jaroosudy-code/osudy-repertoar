<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Song extends Model
{
    protected $fillable = ['name', 'duration_seconds', 'color', 'tempo', 'bpm', 'capo_j', 'type', 'notes', 'lyrics', 'author_lyrics', 'author_music', 'original_artist'];

    public function getDurationFormattedAttribute(): string
    {
        $minutes = intdiv($this->duration_seconds, 60);
        $seconds = $this->duration_seconds % 60;
        return sprintf('%d:%02d', $minutes, $seconds);
    }

    public function setDurationFormattedAttribute(string $value): void
    {
        [$m, $s] = explode(':', $value);
        $this->attributes['duration_seconds'] = (int)$m * 60 + (int)$s;
    }

    public function bands()
    {
        return $this->belongsToMany(Band::class, 'band_song')
            ->withPivot(['added_by_user_id'])
            ->withTimestamps();
    }

    public function setlistSongs()
    {
        return $this->hasMany(SetlistSong::class);
    }
}
