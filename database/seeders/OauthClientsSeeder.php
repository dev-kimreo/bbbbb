<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OauthClientsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        DB::table('oauth_clients')->insert([
            'name' => 'qpicki_front',
            'secret' => 'W6dubFlWMNIy85Wdv1b4jx21NW43m5VC2yHB8Oy0',
            'provider' => 'users',
            'redirect' => 'http://localhost',
            'personal_access_client' => 0,
            'password_client' => 1
        ]);
    }
}
