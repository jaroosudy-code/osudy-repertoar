<?php

namespace Database\Seeders;

use App\Models\Band;
use App\Models\Setlist;
use App\Models\Song;
use App\Models\User;
use Illuminate\Database\Seeder;

class BandSeeder extends Seeder
{
    public function run(): void
    {
        $band = Band::firstOrCreate(
            ['slug' => 'osudy'],
            ['name' => 'Osudy', 'description' => 'Skupina Osudy']
        );

        // Každý používateľ → člen kapely s právami z jeho globálnej roly
        User::with('role')->each(function (User $user) use ($band) {
            if ($band->users()->where('user_id', $user->id)->exists()) {
                return;
            }

            $permissions = $user->role?->permissions ?? [];
            $isBandAdmin = $user->isAdmin();

            $band->users()->attach($user->id, [
                'permissions'   => json_encode($permissions),
                'is_band_admin' => $isBandAdmin,
            ]);
        });

        // Všetky piesne → kapela Osudy
        Song::each(function (Song $song) use ($band) {
            if (!$band->songs()->where('song_id', $song->id)->exists()) {
                $band->songs()->attach($song->id);
            }
        });

        // Všetky playlisty bez kapely → Osudy
        Setlist::whereNull('band_id')->update(['band_id' => $band->id]);

        $this->command->info('Kapela "' . $band->name . '" pripravena. Clenov: ' . $band->users()->count() . ', Piesni: ' . $band->songs()->count());
    }
}
