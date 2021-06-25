<?php

namespace Database\Seeders;

use App\Models\Manager;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class EmailTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $manager = Manager::first();
        //
        DB::table('email_templates')->insert(
            [
                [
                    'user_id' => $manager->id,
                    'code' => 'Users.EmailVerification',
                    'name' => '[회원] 이메일 인증',
                    'enable' => 1,
                    'ignore_agree' => 1,
                    'title' => '이메일 인증 메일입니다.',
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now()
                ],
                [
                    'user_id' => $manager->id,
                    'code' => 'Users.VerifyPassword',
                    'name' => '[회원] 비밀번호 찾기 인증',
                    'enable' => 1,
                    'ignore_agree' => 1,
                    'title' => '비밀번호 찾기 인증 메일입니다.',
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now()
                ]
            ]
        );
    }
}
