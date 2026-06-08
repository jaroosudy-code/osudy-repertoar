<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (session('site_authenticated')) {
            return redirect()->route('songs.index');
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate(['password' => 'required']);

        $hash = Setting::get('site_password');

        if ($hash && Hash::check($request->password, $hash)) {
            $request->session()->put('site_authenticated', true);
            return redirect()->intended(route('songs.index'));
        }

        return back()->withErrors(['password' => 'Nespravne heslo.']);
    }

    public function logout(Request $request)
    {
        $request->session()->forget('site_authenticated');
        return redirect()->route('login');
    }

    public function showSettings()
    {
        return view('auth.settings');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'new_password'     => 'required|min:6|confirmed',
        ]);

        $hash = Setting::get('site_password');

        if (!$hash || !Hash::check($request->current_password, $hash)) {
            return back()->withErrors(['current_password' => 'Aktualne heslo je nespravne.']);
        }

        Setting::set('site_password', Hash::make($request->new_password));

        return back()->with('success', 'Heslo bolo zmenene. Oznam ho ostatnym clenom kapely.');
    }
}
