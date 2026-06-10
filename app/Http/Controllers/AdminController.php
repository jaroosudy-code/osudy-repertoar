<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

class AdminController extends Controller
{
    // ── Users ─────────────────────────────────────────────────────────────────

    public function usersIndex()
    {
        $users = User::with('role')->orderBy('name')->get();
        $roles = Role::orderBy('name')->get();
        return view('admin.users.index', compact('users', 'roles'));
    }

    public function usersCreate()
    {
        $roles = Role::orderBy('name')->get();
        return view('admin.users.create', compact('roles'));
    }

    public function usersStore(Request $request)
    {
        $data = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users',
            'password' => ['required', Password::min(6)],
            'role_id'  => 'required|exists:roles,id',
        ], [
            'email.unique'   => 'Táto emailová adresa je už obsadená.',
            'role_id.exists' => 'Vybraná rola neexistuje.',
        ]);

        User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => Hash::make($data['password']),
            'role_id'  => $data['role_id'],
        ]);

        return redirect()->route('admin.users.index')
            ->with('success', 'Používateľ „' . $data['name'] . '" bol vytvorený.');
    }

    public function usersEdit(User $user)
    {
        $roles = Role::orderBy('name')->get();
        return view('admin.users.edit', compact('user', 'roles'));
    }

    public function usersUpdate(Request $request, User $user)
    {
        $rules = [
            'name'    => 'required|string|max:255',
            'email'   => 'required|email|unique:users,email,' . $user->id,
            'role_id' => 'required|exists:roles,id',
        ];

        if ($request->filled('password')) {
            $rules['password'] = ['required', Password::min(6)];
        }

        $data = $request->validate($rules, [
            'email.unique' => 'Táto emailová adresa je už obsadená.',
        ]);

        $user->name    = $data['name'];
        $user->email   = $data['email'];
        $user->role_id = $data['role_id'];

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->save();

        return redirect()->route('admin.users.index')
            ->with('success', 'Používateľ „' . $user->name . '" bol aktualizovaný.');
    }

    public function usersDestroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Nemôžeš zmazať vlastný účet.');
        }
        $name = $user->name;
        $user->delete();
        return redirect()->route('admin.users.index')
            ->with('success', 'Používateľ „' . $name . '" bol zmazaný.');
    }

    // ── Roles ─────────────────────────────────────────────────────────────────

    public function rolesIndex()
    {
        $roles = Role::withCount('users')->orderBy('name')->get();
        return view('admin.roles.index', compact('roles'));
    }

    public function rolesCreate()
    {
        return view('admin.roles.create');
    }

    public function rolesStore(Request $request)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:100',
            'permissions' => 'nullable|array',
            'permissions.*' => 'string|in:' . implode(',', Role::ALL_PERMISSIONS),
        ]);

        $slug = Str::slug($data['name'], '_');

        if (Role::where('slug', $slug)->exists()) {
            $slug .= '_' . time();
        }

        Role::create([
            'name'        => $data['name'],
            'slug'        => $slug,
            'permissions' => $data['permissions'] ?? [],
        ]);

        return redirect()->route('admin.roles.index')
            ->with('success', 'Rola „' . $data['name'] . '" bola vytvorená.');
    }

    public function rolesEdit(Role $role)
    {
        return view('admin.roles.edit', compact('role'));
    }

    public function rolesUpdate(Request $request, Role $role)
    {
        if ($role->isAdmin()) {
            return back()->with('error', 'Admin rolu nie je možné upravovať.');
        }

        $data = $request->validate([
            'name'          => 'required|string|max:100',
            'permissions'   => 'nullable|array',
            'permissions.*' => 'string|in:' . implode(',', Role::ALL_PERMISSIONS),
        ]);

        $role->update([
            'name'        => $data['name'],
            'permissions' => $data['permissions'] ?? [],
        ]);

        return redirect()->route('admin.roles.index')
            ->with('success', 'Rola „' . $role->name . '" bola aktualizovaná.');
    }

    public function rolesDestroy(Role $role)
    {
        if ($role->isAdmin()) {
            return back()->with('error', 'Admin rolu nie je možné zmazať.');
        }

        if ($role->users()->count() > 0) {
            return back()->with('error', 'Rolu nie je možné zmazať, kým ju má priradených ' . $role->users()->count() . ' používateľ/ov.');
        }

        $name = $role->name;
        $role->delete();
        return redirect()->route('admin.roles.index')
            ->with('success', 'Rola „' . $name . '" bola zmazaná.');
    }
}
