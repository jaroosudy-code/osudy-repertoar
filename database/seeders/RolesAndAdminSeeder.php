<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class RolesAndAdminSeeder extends Seeder
{
    public function run(): void
    {
        // Predvolené roly
        $admin = Role::firstOrCreate(['slug' => 'admin'], [
            'name'        => 'Admin',
            'permissions' => Role::ALL_PERMISSIONS,
        ]);

        Role::firstOrCreate(['slug' => 'editor'], [
            'name'        => 'Editor',
            'permissions' => [
                'songs.create', 'songs.edit',
                'chords.edit',
                'setlists.create', 'setlists.edit_own', 'setlists.delete_own',
                'setlists.edit_all', 'setlists.delete_all',
                'colors.manage',
            ],
        ]);

        Role::firstOrCreate(['slug' => 'clen'], [
            'name'        => 'Člen',
            'permissions' => [
                'setlists.create', 'setlists.edit_own', 'setlists.delete_own',
            ],
        ]);

        Role::firstOrCreate(['slug' => 'zaklad'], [
            'name'        => 'Základ',
            'permissions' => [],
        ]);

        // Obnoviť existujúci hash hesla webu pre admin účet (ak existuje)
        $existingHash = null;
        if (class_exists(Setting::class)) {
            try {
                $existingHash = Setting::get('site_password');
            } catch (\Throwable) {}
        }

        User::firstOrCreate(
            ['email' => 'jaro@skupinaosudy.sk'],
            [
                'name'     => 'Jaro',
                'password' => $existingHash ?? Hash::make('Osudy2024!'),
                'role_id'  => $admin->id,
            ]
        );

        // Priradenie admin role existujúcemu admin účtu ak ešte nemá rolu
        User::where('email', 'jaro@skupinaosudy.sk')
            ->whereNull('role_id')
            ->update(['role_id' => $admin->id]);
    }
}
