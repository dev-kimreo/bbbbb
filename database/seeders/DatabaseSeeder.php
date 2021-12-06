<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call(
            [
                SolutionSeeder::class,
                UserAndManagerSeeder::class,
                OauthClientsSeeder::class,
                BoardsSeeder::class,
                BoardOptionsSeeder::class,
                ExceptionSeeder::class,
                BackofficeMenuSeeder::class,
                EmailTemplateSeeder::class,
                ExhibitionsSeeder::class,
                WidgetSeeder::class,
                ComponentTypeSeeder::class,
                TestEditorSeeder::class,
//                ComponentsAndThemesSeeder::class
            ]
        );

        // \App\Models\Users\User::factory(10)->create();
    }
}

