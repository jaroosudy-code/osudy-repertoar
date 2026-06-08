<?php

namespace App\Http\Controllers;

use App\Models\Setlist;
use App\Models\SetlistSong;
use Illuminate\Http\Request;

class SetlistSongController extends Controller
{
    public function store(Request $request, Setlist $setlist)
    {
        $data = $request->validate([
            'song_id'  => 'required|exists:songs,id',
            'round_id' => 'nullable|exists:rounds,id',
        ]);

        $roundId = $data['round_id'] ?? null;

        $maxPosition = SetlistSong::where('setlist_id', $setlist->id)
            ->where('round_id', $roundId)
            ->max('order_position') ?? -1;

        $entry = SetlistSong::create([
            'setlist_id'     => $setlist->id,
            'round_id'       => $roundId,
            'song_id'        => $data['song_id'],
            'order_position' => $maxPosition + 1,
        ]);

        $entry->load('song');

        return response()->json([
            'id'               => $entry->id,
            'song_id'          => $entry->song_id,
            'round_id'         => $entry->round_id,
            'order_position'   => $entry->order_position,
            'name'             => $entry->song->name,
            'duration_seconds' => $entry->song->duration_seconds,
            'duration_formatted' => $entry->song->duration_formatted,
            'color'            => $entry->song->color,
            'tempo'            => $entry->song->tempo,
            'type'             => $entry->song->type,
        ]);
    }

    public function destroy(Setlist $setlist, SetlistSong $entry)
    {
        $entry->delete();
        return response()->json(['ok' => true]);
    }

    public function reorder(Request $request, Setlist $setlist)
    {
        $request->validate([
            'entries'            => 'required|array',
            'entries.*.id'       => 'required|integer',
            'entries.*.round_id' => 'nullable|integer',
            'entries.*.position' => 'required|integer',
        ]);

        foreach ($request->entries as $item) {
            SetlistSong::where('id', $item['id'])
                ->where('setlist_id', $setlist->id)
                ->update([
                    'round_id'       => $item['round_id'],
                    'order_position' => $item['position'],
                ]);
        }

        return response()->json(['ok' => true]);
    }
}
