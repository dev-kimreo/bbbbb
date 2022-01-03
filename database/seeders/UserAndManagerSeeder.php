<?php

namespace Database\Seeders;

use App\Models\Solution;
use App\Models\Users\User;
use App\Models\Users\UserSolution;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

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
                    'password' => Hash::make('qlfej123!'),
                    'grade' => 999,
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
                ],
                [
                    'id' => 5,
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
                'name' => '큐픽어드민',
                'email' => 'qpick@qpicki.com'
            ],
            [
                'user_id' => 2,
                'name' => '홍길동',
                'email' => 'honggildong@test.qpicki.com'
            ],
            [
                 'user_id' => 3,
                 'name' => '김삿갓',
                 'email' => 'kimsatgat@test.qpicki.com'
            ],
            [
                 'user_id' => 4,
                 'name' => '신도림',
                 'email' => 'sindorim@test.qpicki.com'
            ]
        ]);

        DB::table('user_privacy_inactive')->insertOrIgnore([
            [
                'user_id' => 5,
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
                ],
                [
                    'user_id' => '3',
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
                    'authority_id' => '1',
                    'created_at' => Carbon::now()
                ],
                [
                    'user_id' => '3',
                    'authority_id' => '2',
                    'created_at' => Carbon::now()
                ]
            ]
        );

        // partner
        DB::table('user_partners')->insertOrIgnore(
            [
                [
                    'user_id' => '1',
                    'name' => '큐픽-본사계정',
                    'created_at' => Carbon::now()
                ],
                [
                    'user_id' => '2',
                    'name' => '큐픽-일반파트너',
                    'created_at' => Carbon::now()
                ]
            ]
        );

        // User Solution
        for ($i = 0; $i < 7; $i++) {
            $user = User::skip(rand(1, User::count()) - 1)->first();
            $solution = Solution::skip(rand(1, Solution::count()) - 1)->first();

            UserSolution::factory()
                ->for($user, 'user')
                ->for($solution, 'solution')
                ->create();
        }

        UserSolution::query()->create(
            [
                'user_id' => 1,
                'solution_id' => Solution::query()->where('name', '카페24')->first()->id,
                /*
                'type' => '남성의류',
                'name' => '라파누스몰',
                'url' => 'https://raphanus.cafe24.com',
                */
                'solution_user_id' => 'raphanus',
                'apikey' => 'pRByfNKnDaQKRevR5c8DiA'
            ]
        );
    }
}
