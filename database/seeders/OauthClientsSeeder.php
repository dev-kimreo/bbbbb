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
        $insArrs = [
            [
                "name" => 'qpicki_front',
                "secret" => 'W6dubFlWMNIy85Wdv1b4jx21NW43m5VC2yHB8Oy0',
                "provider" => 'users',
                "redirect" => 'http://localhost',
                "personal_access_client" => 0,
                "password_client" => 1
            ],
            [
                "name" => 'qpicki_crm',
                "secret" => '4QsJFRc8UaN63F5fvkxgvNXTnYg6ripWKEOHFiUU',
                "provider" => 'users',
                "redirect" => 'http://localhost',
                "personal_access_client" => 0,
                "password_client" => 1
            ]
        ];
        //
        foreach ($insArrs as $v) {
            DB::table('oauth_clients')->insert($v);
        }
    }
}
