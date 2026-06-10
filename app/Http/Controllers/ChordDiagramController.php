<?php

namespace App\Http\Controllers;

use App\Models\ChordDiagram;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ChordDiagramController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $name   = $request->query('name', '');
        $songId = $request->query('song_id');

        $chord = $this->find($name, $songId);
        return response()->json($chord);
    }

    public function upsert(Request $request): JsonResponse
    {
        abort_unless(auth()->user()->hasPermission('chords.edit'), 403, 'Nemáš oprávnenie upravovať akordy.');

        $data = $request->validate([
            'name'               => 'required|string|max:20',
            'scope'              => 'required|in:song,global',
            'song_id'            => 'nullable|integer|exists:songs,id',
            'frets'              => 'required|array|size:6',
            'frets.*'            => 'integer|min:-1|max:24',
            'fingers'            => 'required|array|size:6',
            'fingers.*'          => 'integer|min:0|max:4',
            'starting_fret'      => 'integer|min:1|max:20',
            'barre_fret'         => 'nullable|integer|min:1|max:20',
            'barre_from_string'  => 'nullable|integer|min:0|max:5',
            'barre_to_string'    => 'nullable|integer|min:0|max:5',
        ]);

        $attributes = collect($data)->except(['scope', 'song_id'])->toArray();

        if ($data['scope'] === 'song' && !empty($data['song_id'])) {
            $chord = $this->findExact($data['name'], $data['song_id']);
            if ($chord) {
                $chord->update($attributes);
            } else {
                $chord = ChordDiagram::create(array_merge($attributes, ['song_id' => $data['song_id']]));
            }
        } else {
            $chord = $this->findExact($data['name'], null);
            if ($chord) {
                $chord->update($attributes + ['song_id' => null]);
            } else {
                $chord = ChordDiagram::create(array_merge($attributes, ['song_id' => null]));
            }
        }

        return response()->json($chord);
    }

    // ── helpers ──────────────────────────────────────────────────────────────

    private function find(string $name, ?string $songId): ?ChordDiagram
    {
        if ($songId) {
            $chord = $this->findExact($name, (int) $songId);
            if ($chord) return $chord;

            $norm = $this->normalize($name);
            if ($norm !== $name) {
                $chord = $this->findExact($norm, (int) $songId);
                if ($chord) return $chord;
            }
        }

        $chord = $this->findExact($name, null);
        if ($chord) return $chord;

        $norm = $this->normalize($name);
        if ($norm !== $name) {
            return $this->findExact($norm, null);
        }

        return null;
    }

    private function findExact(string $name, ?int $songId): ?ChordDiagram
    {
        $q = ChordDiagram::where('name', $name);
        $songId ? $q->where('song_id', $songId) : $q->whereNull('song_id');
        return $q->first();
    }

    private function normalize(string $name): string
    {
        $name = preg_replace('/mi(\d*)$/', 'm$1', $name);
        $name = preg_replace('/maj$/', '', $name);
        return $name;
    }
}
