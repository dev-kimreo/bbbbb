<?php

namespace Database\Seeders;

use App\Models\Solution;
use App\Models\Users\User;
use App\Models\Users\UserPrivacyActive;
use App\Models\Users\UserSite;
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
                'name' => '???????????????',
                'email' => 'qpick@qpicki.com'
            ],
            [
                'user_id' => 2,
                'name' => '?????????',
                'email' => 'honggildong@test.qpicki.com'
            ],
            [
                 'user_id' => 3,
                 'name' => '?????????',
                 'email' => 'kimsatgat@test.qpicki.com'
            ],
            [
                 'user_id' => 4,
                 'name' => '?????????',
                 'email' => 'sindorim@test.qpicki.com'
            ]
        ]);

        DB::table('user_privacy_inactive')->insertOrIgnore([
            [
                'user_id' => 5,
                'name' => '?????????',
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
                    'title' => '??????????????????',
                    'display_name' => '?????????',
                    'memo' => '?????? ????????? ??????',
                    'created_at' => Carbon::now()
                ],
                [
                    'code' => '2',
                    'title' => '???????????????',
                    'display_name' => '?????????',
                    'memo' => '?????? ????????????',
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
                    'name' => '??????-????????????',
                    'created_at' => Carbon::now()
                ],
                [
                    'user_id' => '2',
                    'name' => '??????-???????????????',
                    'created_at' => Carbon::now()
                ]
            ]
        );

        // User Solution
        for ($i = 0; $i < 7; $i++) {
            $user = User::skip(rand(1, User::count()) - 1)->first();
            $solution = Solution::skip(rand(1, Solution::count()) - 1)->first();
            $userSolution = UserSolution::factory()
                ->for($user, 'user')
                ->for($solution, 'solution')
                ->create();

            UserSite::query()->create(
                [
                    'user_id' => $user->id,
                    'user_solution_id' => $userSolution->id,
                    'name' => substr(md5(rand(0,10000)), 0, rand(6,14)) . array_rand(array_flip(['???', '??????', '???'])),
                    'url' => 'https://' . substr(md5(rand(0,10000)), 0, rand(6,14)) . '.com',
                    'biz_type' => array_rand(array_flip(['????????????', '????????????', '??????', '??????']))
                ]
            );
        }
    }
}
