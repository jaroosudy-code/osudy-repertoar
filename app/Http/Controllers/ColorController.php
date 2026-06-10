<?php

namespace App\Http\Controllers;

use App\Models\Color;
use Illuminate\Http\Request;

class ColorController extends Controller
{
    public function index()
    {
        return response()->json(Color::orderBy('name')->get());
    }

    public function store(Request $request)
    {
        abort_unless(auth()->user()->hasPermission('colors.manage'), 403);

        $data = $request->validate([
            'name'     => 'required|string|max:60',
            'hex_code' => 'required|regex:/^#[0-9a-fA-F]{6}$/',
        ]);

        $color = Color::create($data);
        return response()->json($color);
    }

    public function destroy(Color $color)
    {
        abort_unless(auth()->user()->hasPermission('colors.manage'), 403);
        $color->delete();
        return response()->json(['ok' => true]);
    }
}
