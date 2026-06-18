<?php

namespace App\Http\Controllers;

use App\Models\Band;

class BandController extends Controller
{
    public function select()
    {
        $bands = auth()->user()->bands()->get();
        return view('bands.select', compact('bands'));
    }

    public function noBand()
    {
        return view('bands.no-band');
    }

    public function switch(Band $band)
    {
        $user = auth()->user();

        if (! $user->isAdmin() && ! $user->bands()->where('band_id', $band->id)->exists()) {
            abort(403);
        }

        session(['current_band_id' => $band->id]);

        return redirect()->route('songs.index');
    }
}
