<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class BackofficeMenuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        DB::table('backoffice_menus')->insert(
            [
                [
                    'name' => '대시보드',
                    'depth' => 1,
                    'parent' => 0,
                    'last' => 1,
                    'sort' => 1,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now()
                ],
                [
                    'name' => '회원관리',
                    'depth' => 1,
                    'parent' => 0,
                    'last' => 1,
                    'sort' => 1,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now()
                ],
                [
                    'name' => '라이브러리',
                    'depth' => 1,
                    'parent' => 0,
                    'last' => 0,
                    'sort' => 1,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now()
                ],
                [
                    'name' => '컴포넌트관리',
                    'depth' => 2,
                    'parent' => 3,
                    'last' => 1,
                    'sort' => 1,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now()
                ],
                [
                    'name' => '플러그인관리',
                    'depth' => 2,
                    'parent' => 3,
                    'last' => 1,
                    'sort' => 1,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now()
                ],
                [
                    'name' => '메일템플릿관리',
                    'depth' => 2,
                    'parent' => 3,
                    'last' => 1,
                    'sort' => 1,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now()
                ],
                [
                    'name' => '테마관리',
                    'depth' => 1,
                    'parent' => 0,
                    'last' => 1,
                    'sort' => 1,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now()
                ],
                [
                    'name' => '플러그인관리',
                    'depth' => 1,
                    'parent' => 0,
                    'last' => 1,
                    'sort' => 1,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now()
                ],
            ]
        );
    }
}
