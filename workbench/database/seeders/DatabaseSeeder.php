<?php

declare(strict_types=1);

namespace Workbench\Database\Seeders;

use BBSLab\NovaTranslation\Models\Locale;
use Illuminate\Database\Seeder;
use Workbench\Database\Factories\UserFactory;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        UserFactory::new()->create([
            'name' => 'Laravel Nova',
            'email' => 'nova@laravel.com',
        ]);

        UserFactory::new()->times(10)->create();

        Locale::query()->create([
            'iso' => 'en',
            'label' => 'English',
            'available_in_api' => true,
        ]);

        Locale::query()->create([
            'iso' => 'fr',
            'label' => 'Français',
            'available_in_api' => true,
        ]);

        Locale::query()->create([
            'iso' => 'es',
            'label' => 'Español',
            'available_in_api' => true,
        ]);
    }
}
