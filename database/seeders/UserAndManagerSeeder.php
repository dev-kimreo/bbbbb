<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserAndManagerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('users')->insertOrIgnore(
            [
                [
                    'id' => 1,
                    'password' => Hash::make('password!1'),
                    'grade' => 1,
                    'email_verified_at' => Carbon::now(),
                    'inactivated_at' => null,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now()
                ],
                [
                    'id' => 2,
                    'password' => Hash::make('password!1'),
                    'grade' => 1,
                    'email_verified_at' => Carbon::now(),
                    'inactivated_at' => null,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now()
                ],
                [
                    'id' => 3,
                    'password' => Hash::make('password!1'),
                    'grade' => 0,
                    'email_verified_at' => Carbon::now(),
                    'inactivated_at' => null,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now()
                ],
                [
                    'id' => 4,
                    'password' => Hash::make('password!1'),
                    'grade' => 0,
                    'email_verified_at' => Carbon::now(),
                    'inactivated_at' => Carbon::now(),
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now()
                ]
            ]
        );

        DB::table('user_privacy_active')->insertOrIgnore([
             [
                 'user_id' => 1,
                 'name' => '홍길동',
                 'email' => 'honggildong@test.qpicki.com'
             ],
             [
                 'user_id' => 2,
                 'name' => '김삿갓',
                 'email' => 'kimsatgat@test.qpicki.com'
             ],
             [
                 'user_id' => 3,
                 'name' => '신도림',
                 'email' => 'sindorim@test.qpicki.com'
             ]
        ]);

        DB::table('user_privacy_inactive')->insertOrIgnore([
            [
                'user_id' => 4,
                'name' => '비활성',
                'email' => 'inactivated@test.qpicki.com'
            ]
        ]);

        DB::table('user_advertising_agrees')->insertOrIgnore(
            [
                [
                    'user_id' => '1',
                    'created_at' => Carbon::now()
                ],
                [
                    'user_id' => '2',
                    'created_at' => Carbon::now()
                ]
            ]
        );
        DB::table('authorities')->insertOrIgnore(
            [
                [
                    'code' => '1',
                    'title' => '시스템관리자',
                    'display_name' => '운영자',
                    'memo' => '큐픽 사이트 운영',
                    'created_at' => Carbon::now()
                ],
                [
                    'code' => '2',
                    'title' => '운영관리자',
                    'display_name' => '운영자',
                    'memo' => '일반 운영관리',
                    'created_at' => Carbon::now()
                ]
            ]
        );
        DB::table('managers')->insertOrIgnore(
            [
                [
                    'user_id' => '1',
                    'authority_id' => '1',
                    'created_at' => Carbon::now()
                ],
                [
                    'user_id' => '2',
                    'authority_id' => '2',
                    'created_at' => Carbon::now()
                ]
            ]
        );
    }
}
