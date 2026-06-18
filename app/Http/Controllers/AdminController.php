<?php

namespace App\Http\Controllers;

use App\Models\Band;
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
        $users = User::with('role', 'bands')->orderBy('name')->get();
        $roles = Role::orderBy('name')->get();
        return view('admin.users.index', compact('users', 'roles'));
    }

    public function usersCreate()
    {
        $roles = Role::orderBy('name')->get();
        $bands = Band::orderBy('name')->get();
        return view('admin.users.create', compact('roles', 'bands'));
    }

    public function usersStore(Request $request)
    {
        $data = $request->validate([
            'name'                   => 'required|string|max:255',
            'email'                  => 'required|email|unique:users',
            'password'               => ['required', Password::min(6)],
            'role_id'                => 'required|exists:roles,id',
            'band_ids'               => 'nullable|array',
            'band_ids.*'             => 'exists:bands,id',
            'band_admin'             => 'nullable|array',
            'band_permissions'       => 'nullable|array',
            'band_permissions.*'     => 'array',
            'band_permissions.*.*'   => 'string|in:' . implode(',', Role::ALL_PERMISSIONS),
        ], [
            'email.unique'   => 'Táto emailová adresa je už obsadená.',
            'role_id.exists' => 'Vybraná rola neexistuje.',
        ]);

        $user = User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => Hash::make($data['password']),
            'role_id'  => $data['role_id'],
        ]);

        foreach ($data['band_ids'] ?? [] as $bandId) {
            $user->bands()->attach($bandId, [
                'is_band_admin' => isset($data['band_admin'][$bandId]),
                'permissions'   => json_encode($data['band_permissions'][$bandId] ?? []),
            ]);
        }

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

    // ── Bands ─────────────────────────────────────────────────────────────────

    public function bandsIndex()
    {
        $bands = Band::withCount(['users', 'songs', 'setlists'])->orderBy('name')->get();
        return view('admin.bands.index', compact('bands'));
    }

    public function bandsCreate()
    {
        return view('admin.bands.form', ['band' => null]);
    }

    public function bandsStore(Request $request)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:100',
            'description' => 'nullable|string|max:500',
        ]);

        $slug = Str::slug($data['name'], '_');
        if (Band::where('slug', $slug)->exists()) {
            $slug .= '_' . time();
        }

        Band::create([
            'name'        => $data['name'],
            'slug'        => $slug,
            'description' => $data['description'] ?? null,
        ]);

        return redirect()->route('admin.bands.index')
            ->with('success', 'Kapela „' . $data['name'] . '" bola vytvorená.');
    }

    public function bandsEdit(Band $band)
    {
        $band->load(['users.role']);
        $allUsers = User::with('role')->orderBy('name')->get();
        return view('admin.bands.form', compact('band', 'allUsers'));
    }

    public function bandsUpdate(Request $request, Band $band)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:100',
            'description' => 'nullable|string|max:500',
        ]);

        $band->update($data);

        return redirect()->route('admin.bands.edit', $band)
            ->with('success', 'Kapela bola aktualizovaná.');
    }

    public function bandsDestroy(Band $band)
    {
        if ($band->songs()->count() > 0 || $band->setlists()->count() > 0) {
            return back()->with('error', 'Kapelu nie je možné zmazať, kým má piesne alebo playlisty.');
        }

        $name = $band->name;
        $band->delete();

        return redirect()->route('admin.bands.index')
            ->with('success', 'Kapela „' . $name . '" bola zmazaná.');
    }

    public function bandsAttachUser(Request $request, Band $band)
    {
        $data = $request->validate([
            'user_id'       => 'required|exists:users,id',
            'is_band_admin' => 'boolean',
            'permissions'   => 'nullable|array',
            'permissions.*' => 'string|in:' . implode(',', Role::ALL_PERMISSIONS),
        ]);

        if ($band->users()->where('user_id', $data['user_id'])->exists()) {
            return back()->with('error', 'Používateľ je už členom tejto kapely.');
        }

        $band->users()->attach($data['user_id'], [
            'is_band_admin' => $data['is_band_admin'] ?? false,
            'permissions'   => json_encode($data['permissions'] ?? []),
        ]);

        return back()->with('success', 'Používateľ bol pridaný do kapely.');
    }

    public function bandsDetachUser(Band $band, User $user)
    {
        $band->users()->detach($user->id);
        return back()->with('success', 'Používateľ bol odobratý z kapely.');
    }

    public function bandsUpdateUserPermissions(Request $request, Band $band, User $user)
    {
        $data = $request->validate([
            'is_band_admin' => 'boolean',
            'permissions'   => 'nullable|array',
            'permissions.*' => 'string|in:' . implode(',', Role::ALL_PERMISSIONS),
        ]);

        $band->users()->updateExistingPivot($user->id, [
            'is_band_admin' => $data['is_band_admin'] ?? false,
            'permissions'   => json_encode($data['permissions'] ?? []),
        ]);

        return back()->with('success', 'Práva používateľa „' . $user->name . '" boli aktualizované.');
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
