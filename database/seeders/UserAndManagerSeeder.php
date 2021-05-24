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
                    'email' => Str::random(10) . '@test.qpicki.com',
                    'password' => Hash::make('password'),
                ],
                [
                    'name' => '김삿갓',
                    'email' => Str::random(10) . '@test.qpicki.com',
                    'password' => Hash::make('password'),
                ],
                [
                    'name' => '신도림',
                    'email' => Str::random(10) . '@test.qpicki.com',
                    'password' => Hash::make('password'),
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
                    'authority_id' => '1'
                ],
                [
                    'user_id' => '2',
                    'authority_id' => '2'
                ]
            ]
        );
    }
}
