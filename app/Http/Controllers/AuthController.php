<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (auth()->check()) return redirect()->route('songs.index');
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ], [
            'email.required'    => 'Zadaj emailovú adresu.',
            'email.email'       => 'Neplatný formát emailu.',
            'password.required' => 'Zadaj heslo.',
        ]);

        if (Auth::attempt(
            ['email' => $request->email, 'password' => $request->password],
            $request->boolean('remember')
        )) {
            $request->session()->regenerate();
            return redirect()->intended(route('songs.index'));
        }

        return back()
            ->withErrors(['email' => 'Nesprávny email alebo heslo.'])
            ->withInput($request->only('email'));
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }

    public function showSettings()
    {
        return view('auth.settings');
    }

    public function toggleInvisible(Request $request)
    {
        $user = auth()->user();
        $user->update(['is_invisible' => !$user->is_invisible]);
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['is_invisible' => $user->is_invisible]);
        }
        return back()->with('success', $user->is_invisible ? 'Neviditeľný režim zapnutý.' : 'Neviditeľný režim vypnutý.');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'new_password'     => 'required|min:6|confirmed',
        ], [
            'new_password.min'       => 'Nové heslo musí mať aspoň 6 znakov.',
            'new_password.confirmed' => 'Heslá sa nezhodujú.',
        ]);

        if (!Hash::check($request->current_password, auth()->user()->password)) {
            return back()->withErrors(['current_password' => 'Aktuálne heslo je nesprávne.']);
        }

        auth()->user()->update(['password' => Hash::make($request->new_password)]);

        return back()->with('success', 'Heslo bolo zmenené.');
    }
}
