<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class OauthClientsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('oauth_clients')->insert(
            [
                [
                    "id" => '1',
                    "name" => 'qpicki_front',
                    "secret" => 'W6dubFlWMNIy85Wdv1b4jx21NW43m5VC2yHB8Oy0',
                    "provider" => 'users',
                    "redirect" => 'http://localhost',
                    "personal_access_client" => 1,
                    "password_client" => 1
                ],
                [
                    "id" => '2',
                    "name" => 'qpicki_crm',
                    "secret" => '4QsJFRc8UaN63F5fvkxgvNXTnYg6ripWKEOHFiUU',
                    "provider" => 'users',
                    "redirect" => 'http://localhost',
                    "personal_access_client" => 0,
                    "password_client" => 1
                ],
                [
                    "id" => '3',
                    "name" => 'qpicki_partner',
                    "secret" => '1qHu1PnRScO20Lh6OvM4Rso83jypcx0rQ0B3rY8Y',
                    "provider" => 'users',
                    "redirect" => 'http://localhost',
                    "personal_access_client" => 0,
                    "password_client" => 1
                ]
            ]
        );

        DB::table('oauth_personal_access_clients')->insert(
            [
                [
                    "client_id" => '1',
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now()
                ]
            ]
        );


    }
}
