<?php

namespace BBS\Nova\Translation\Seeders;

use BBS\Nova\Translation\Models\Locale;
use Illuminate\Database\Seeder;

class LocalesTableSeeder extends Seeder
{
    /**
     * Table seeder.
     *
     * @return void
     */
    public function run()
    {
        $locales = ['en' => 'English', 'fr' => 'Français'];
        $defaultIso = array_keys($locales)[0];

        foreach ($locales as $iso => $label) {
            Locale::query()->create([
                'iso' => $iso,
                'label' => $label,
                'fallback_id' => ($iso === $defaultIso) ? null : 1,
                'available_in_api' => true,
            ]);
        }
    }
}
