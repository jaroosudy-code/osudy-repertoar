<?php

namespace App\Http\Controllers;

use App\Models\Setlist;
use App\Models\Song;
use Illuminate\Http\Request;

class SetlistController extends Controller
{
    public function index()
    {
        $bandId = session('current_band_id');
        $setlists = Setlist::withCount('setlistSongs')
            ->where('band_id', $bandId)
            ->latest()
            ->get();
        return view('setlists.index', compact('setlists'));
    }

    public function create()
    {
        abort_unless(auth()->user()->hasPermission('setlists.create'), 403, 'Nemáš oprávnenie vytvárať playlisty.');
        return view('setlists.create');
    }

    public function store(Request $request)
    {
        abort_unless(auth()->user()->hasPermission('setlists.create'), 403);

        $data = $request->validate([
            'name'       => 'required|string|max:255',
            'event_type' => 'required|in:concert,entertainment',
            'event_date' => 'nullable|date',
            'notes'      => 'nullable|string',
        ]);

        $setlist = Setlist::create(array_merge($data, [
            'user_id' => auth()->id(),
            'band_id' => session('current_band_id'),
        ]));

        if ($setlist->event_type === 'entertainment') {
            $setlist->rounds()->create(['name' => 'Kolo 1', 'order_position' => 0]);
        }

        return redirect()->route('setlists.show', $setlist)
            ->with('success', 'Playlist „' . $setlist->name . '" bol vytvorený.');
    }

    public function show(Setlist $setlist)
    {
        $bandId = session('current_band_id') ?? $setlist->band_id;
        $allSongs = Song::whereHas('bands', fn($q) => $q->where('bands.id', $bandId))
            ->orderBy('name')
            ->get();
        $canEdit  = $setlist->canBeEditedBy(auth()->user());

        if ($setlist->event_type === 'entertainment') {
            $rounds = $setlist->rounds()->with(['setlistSongs.song'])->get();
            return view('setlists.show', compact('setlist', 'rounds', 'allSongs', 'canEdit'));
        }

        $entries = $setlist->concertSongs()->with('song')->get();
        return view('setlists.show', compact('setlist', 'allSongs', 'entries', 'canEdit'));
    }

    public function edit(Setlist $setlist)
    {
        abort_unless($setlist->canBeEditedBy(auth()->user()), 403, 'Nemáš oprávnenie upravovať tento playlist.');
        return view('setlists.edit', compact('setlist'));
    }

    public function update(Request $request, Setlist $setlist)
    {
        abort_unless($setlist->canBeEditedBy(auth()->user()), 403);

        $data = $request->validate([
            'name'       => 'required|string|max:255',
            'event_date' => 'nullable|date',
            'notes'      => 'nullable|string',
        ]);

        $setlist->update($data);

        return redirect()->route('setlists.show', $setlist)
            ->with('success', 'Playlist bol aktualizovaný.');
    }

    public function destroy(Setlist $setlist)
    {
        abort_unless($setlist->canBeDeletedBy(auth()->user()), 403, 'Nemáš oprávnenie mazať tento playlist.');
        $name = $setlist->name;
        $setlist->delete();
        return redirect()->route('setlists.index')
            ->with('success', 'Playlist „' . $name . '" bol zmazaný.');
    }
}
