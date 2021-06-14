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
        DB::table('users')->insert(
            [
                [
                    'name' => '홍길동',
                    'email' => 'honggildong@test.qpicki.com',
                    'password' => Hash::make('password!1'),
                    'grade' => 1,
                    'email_verified_at' => Carbon::now(),
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now()
                ],
                [
                    'name' => '김삿갓',
                    'email' => 'kimsatgat@test.qpicki.com',
                    'password' => Hash::make('password!1'),
                    'grade' => 1,
                    'email_verified_at' => Carbon::now(),
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now()
                ],
                [
                    'name' => '신도림',
                    'email' => 'sindorim@test.qpicki.com',
                    'password' => Hash::make('password!1'),
                    'grade' => 0,
                    'email_verified_at' => Carbon::now(),
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now()
                ]
            ]
        );


        DB::table('user_advertising_agrees')->insert(
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
        DB::table('authorities')->insert(
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
        DB::table('managers')->insert(
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
