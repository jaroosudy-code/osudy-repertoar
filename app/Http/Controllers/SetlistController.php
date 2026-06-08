<?php

namespace App\Http\Controllers;

use App\Models\Setlist;
use App\Models\Song;
use Illuminate\Http\Request;

class SetlistController extends Controller
{
    public function index()
    {
        $setlists = Setlist::withCount('setlistSongs')->latest()->get();
        return view('setlists.index', compact('setlists'));
    }

    public function create()
    {
        return view('setlists.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'       => 'required|string|max:255',
            'event_type' => 'required|in:concert,entertainment',
            'event_date' => 'nullable|date',
            'notes'      => 'nullable|string',
        ]);

        $setlist = Setlist::create($data);

        if ($setlist->event_type === 'entertainment') {
            $setlist->rounds()->create(['name' => 'Kolo 1', 'order_position' => 0]);
        }

        return redirect()->route('setlists.show', $setlist)->with('success', 'Setlist "' . $setlist->name . '" bol vytvoreny.');
    }

    public function show(Setlist $setlist)
    {
        $allSongs = Song::orderBy('name')->get();

        if ($setlist->event_type === 'entertainment') {
            $rounds = $setlist->rounds()->with(['setlistSongs.song'])->get();
            return view('setlists.show', compact('setlist', 'rounds', 'allSongs'));
        }

        $entries = $setlist->concertSongs()->with('song')->get();
        return view('setlists.show', compact('setlist', 'allSongs', 'entries'));
    }

    public function edit(Setlist $setlist)
    {
        return view('setlists.edit', compact('setlist'));
    }

    public function update(Request $request, Setlist $setlist)
    {
        $data = $request->validate([
            'name'       => 'required|string|max:255',
            'event_date' => 'nullable|date',
            'notes'      => 'nullable|string',
        ]);

        $setlist->update($data);

        return redirect()->route('setlists.show', $setlist)->with('success', 'Setlist bol aktualizovaný.');
    }

    public function destroy(Setlist $setlist)
    {
        $name = $setlist->name;
        $setlist->delete();
        return redirect()->route('setlists.index')->with('success', 'Setlist "' . $name . '" bol zmazany.');
    }
}
