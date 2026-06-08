<?php

namespace App\Http\Controllers;

use App\Models\Round;
use App\Models\Setlist;
use Illuminate\Http\Request;

class RoundController extends Controller
{
    public function store(Request $request, Setlist $setlist)
    {
        $nextPosition = $setlist->rounds()->max('order_position') + 1;
        $round = $setlist->rounds()->create([
            'name'           => 'Kolo ' . ($nextPosition + 1),
            'order_position' => $nextPosition,
        ]);

        return response()->json(['id' => $round->id, 'name' => $round->name, 'order_position' => $round->order_position]);
    }

    public function update(Request $request, Setlist $setlist, Round $round)
    {
        $data = $request->validate([
            'name'                => 'sometimes|string|max:100',
            'break_after_minutes' => 'sometimes|integer|min:0|max:120',
        ]);

        $round->update($data);

        return response()->json(['ok' => true]);
    }

    public function destroy(Setlist $setlist, Round $round)
    {
        $round->delete();
        return response()->json(['ok' => true]);
    }

    public function reorder(Request $request, Setlist $setlist)
    {
        $request->validate(['order' => 'required|array', 'order.*' => 'integer']);

        foreach ($request->order as $position => $roundId) {
            $setlist->rounds()->where('id', $roundId)->update(['order_position' => $position]);
        }

        return response()->json(['ok' => true]);
    }
}
