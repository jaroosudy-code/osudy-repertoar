<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChordDiagram extends Model
{
    protected $fillable = [
        'song_id', 'name', 'frets', 'fingers', 'starting_fret',
        'barre_fret', 'barre_from_string', 'barre_to_string',
    ];

    protected $casts = [
        'frets'   => 'array',
        'fingers' => 'array',
    ];
}
