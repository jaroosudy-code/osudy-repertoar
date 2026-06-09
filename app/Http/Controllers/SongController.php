<?php

namespace App\Http\Controllers;

use App\Models\Song;
use Illuminate\Http\Request;

class SongController extends Controller
{
    public function index()
    {
        $songs = Song::orderBy('name')->get();
        return view('songs.index', compact('songs'));
    }

    public function create()
    {
        return view('songs.create');
    }

    public function store(Request $request)
    {
        // Normalize time separator: 3.10 or 3,10 → 3:10
        $request->merge([
            'duration_formatted' => str_replace([',', '.'], ':', $request->input('duration_formatted', '')),
        ]);

        $data = $request->validate([
            'name'               => 'required|string|max:255',
            'duration_formatted' => 'required|regex:/^\d+:[0-5]\d$/',
            'color'              => 'required|regex:/^#[0-9a-fA-F]{6}$/',
            'tempo'              => 'required|in:fast,slow',
            'type'               => 'required|in:own,cover',
            'notes'              => 'nullable|string',
            'lyrics'             => 'nullable|string',
            'author_lyrics'      => 'nullable|string|max:255',
            'author_music'       => 'nullable|string|max:255',
            'original_artist'    => 'nullable|string|max:255',
        ], [
            'name.required'               => 'Názov piesne je povinný.',
            'duration_formatted.required' => 'Dĺžka piesne je povinná.',
            'duration_formatted.regex'    => 'Neplatný formát dĺžky – zadaj napr. 3:45.',
            'tempo.required'              => 'Vyberte tempo piesne.',
            'type.required'               => 'Vyberte typ piesne.',
        ]);

        [$m, $s] = explode(':', $data['duration_formatted']);
        $song = Song::create([
            'name'             => $data['name'],
            'duration_seconds' => (int)$m * 60 + (int)$s,
            'color'            => $data['color'],
            'tempo'            => $data['tempo'],
            'type'             => $data['type'],
            'notes'            => $data['notes'] ?? null,
            'lyrics'           => $data['lyrics'] ?? null,
            'author_lyrics'    => $data['author_lyrics'] ?? null,
            'author_music'     => $data['author_music'] ?? null,
            'original_artist'  => $data['type'] === 'cover' ? ($data['original_artist'] ?? null) : null,
        ]);

        return redirect()->route('songs.index')->with('success', 'Piesne "' . $song->name . '" bola pridana.');
    }

    public function show(Song $song)
    {
        return view('songs.show', compact('song'));
    }

    public function edit(Song $song)
    {
        return view('songs.edit', compact('song'));
    }

    public function update(Request $request, Song $song)
    {
        $request->merge([
            'duration_formatted' => str_replace([',', '.'], ':', $request->input('duration_formatted', '')),
        ]);

        $data = $request->validate([
            'name'               => 'required|string|max:255',
            'duration_formatted' => 'required|regex:/^\d+:[0-5]\d$/',
            'color'              => 'required|regex:/^#[0-9a-fA-F]{6}$/',
            'tempo'              => 'required|in:fast,slow',
            'type'               => 'required|in:own,cover',
            'notes'              => 'nullable|string',
            'lyrics'             => 'nullable|string',
            'author_lyrics'      => 'nullable|string|max:255',
            'author_music'       => 'nullable|string|max:255',
            'original_artist'    => 'nullable|string|max:255',
        ], [
            'name.required'               => 'Názov piesne je povinný.',
            'duration_formatted.required' => 'Dĺžka piesne je povinná.',
            'duration_formatted.regex'    => 'Neplatný formát dĺžky – zadaj napr. 3:45.',
            'tempo.required'              => 'Vyberte tempo piesne.',
            'type.required'               => 'Vyberte typ piesne.',
        ]);

        [$m, $s] = explode(':', $data['duration_formatted']);
        $song->update([
            'name'             => $data['name'],
            'duration_seconds' => (int)$m * 60 + (int)$s,
            'color'            => $data['color'],
            'tempo'            => $data['tempo'],
            'type'             => $data['type'],
            'notes'            => $data['notes'] ?? null,
            'lyrics'           => $data['lyrics'] ?? null,
            'author_lyrics'    => $data['author_lyrics'] ?? null,
            'author_music'     => $data['author_music'] ?? null,
            'original_artist'  => $data['type'] === 'cover' ? ($data['original_artist'] ?? null) : null,
        ]);

        return redirect()->route('songs.index')->with('success', 'Piesne "' . $song->name . '" bola aktualizovana.');
    }

    public function destroy(Song $song)
    {
        $name = $song->name;
        $song->delete();
        return redirect()->route('songs.index')->with('success', 'Piesne "' . $name . '" bola zmazana.');
    }
}
