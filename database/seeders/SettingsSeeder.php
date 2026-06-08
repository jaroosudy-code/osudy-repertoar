<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        Setting::set('site_password', Hash::make('Konka14072014'));
    }
}
