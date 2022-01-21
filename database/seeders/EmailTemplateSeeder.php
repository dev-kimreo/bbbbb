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
        $templateRegister = '
            @extends("emails.layout")

            @section("title")
                <span style="color:#54C7A2">큐픽</span>에 가입하기 위한<br />
                인증메일을 보내드립니다.
                @endsection

            @section("contents")
            <b style="font-size:18px;">{{ $user[\'privacy\'][\'name\'] }}</b>님, 큐픽에 가입해주셔서 대단히 감사합니다.<br />
            아래의 링크를 클릭하면 이메일 인증이 완료됩니다.<br />
            큐픽을 이용해 더 쉽게 더 멋진 쇼핑몰을 만들어보세요.<br />
            <br />
            <a href="{{ $data[\'url\'] }}" style="background-color:#54C7A2;width:300px;height:50px;margin:30px auto 50px auto;display:block;color:#fff;font-size:14px;font-weight:bold;text-align:center;text-decoration:none;line-height:50px;font-size:15px">
                이메일 인증 진행하기
            </a><br />
            @endsection
        ';

        $templateInactivatePriorNotice = '
            @extends("emails.layout")

            @section("title")
                회원님의 <span style="color:#54C7A2">큐픽</span> 아이디가<br />
                휴면상태로 전환될 예정입니다.
                @endsection

            @section("contents")
            큐픽은 관계법령에서 정한 개인정보 보호조치에 따라<br />
            1년간 이용기록이 없는 회원님의 아이디를 휴면상태로 전환할 예정입니다.<br />
            휴면상태가 된 아이디의 개인정보는 분리보관됩니다.<br />
            <br />
            서비스를 계속 이용하시려면, 아래 예정일 이전에 로그인을 진행해주세요.<br />
            <br />
            <ul style="padding:15px; background-color:#eee; font-size:15px;">
                <li style="margin-left:15px"><b>대상아이디</b> : {{ $data[\'email\'] }}</li>
                <li style="margin-left:15px"><b>전환예정일</b> : {{ $data[\'dateInactivate\'] }}</li>
                <li style="margin-left:15px"><b>분리보관항목</b> : 이메일 계정, 닉네임</li>
                <li style="margin-left:15px"><b>관련법령</b> : 개인정보 보호법 39조의 6(개인정보의 파기에 대한 특례) 및 개인정보 보호법 시행령 제48조의 5 (개인정보의 파기 등에 관한 특례) (단 다른 법률에 의거하여 별도의 기간을 정하는 경우 관련 법령에 따릅니다.)</li>
            </ul>
            @endsection
        ';

        $templateWithdrawalPriorNotice = '
            @extends("emails.layout")

            @section("title")
                회원님의 <span style="color:#54C7A2">큐픽</span> 아이디가<br />
                장기 미사용으로 탈퇴처리될 예정입니다.
                @endsection

            @section("contents")
            큐픽은 관계법령에서 정한 개인정보 보호조치에 따라<br />
            휴면된 후 1년간 이용기록이 없는 회원님의 아이디를 탈퇴처리할 예정입니다.<br />
            탈퇴처리 된 아이디의 개인정보는 파기됩니다.<br />
            <br />
            서비스를 계속 이용하시려면, 아래 예정일 이전에 로그인을 진행해주세요.<br />
            <br />
            <ul style="padding:15px; background-color:#eee; font-size:15px;">
                <li style="margin-left:15px"><b>대상아이디</b> : {{ $data[\'email\'] }}</li>
                <li style="margin-left:15px"><b>탈퇴예정일</b> : {{ $data[\'dateWithdrawal\'] }}</li>
                <li style="margin-left:15px"><b>파기정보항목</b> : 이메일 계정, 닉네임</li>
                <li style="margin-left:15px"><b>관련법령</b> : 개인정보 보호법 39조의 6(개인정보의 파기에 대한 특례) 및 개인정보 보호법 시행령 제48조의 5 (개인정보의 파기 등에 관한 특례) (단 다른 법률에 의거하여 별도의 기간을 정하는 경우 관련 법령에 따릅니다.)</li>
            </ul>
            @endsection
        ';

        $manager = Manager::first();

        DB::table('email_templates')->insert(
            [
                [
                    'user_id' => $manager->id,
                    'code' => 'Users.EmailVerification',
                    'name' => '[회원] 이메일 인증',
                    'enable' => 1,
                    'ignore_agree' => 1,
                    'title' => '이메일 인증 메일입니다.',
                    'contents' => $templateRegister,
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
                    'contents' => '',
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now()
                ],
                [
                    'user_id' => $manager->id,
                    'code' => 'Users.InactivatePriorNotice',
                    'name' => '[회원] 휴면 계정 전환 안내',
                    'enable' => 1,
                    'ignore_agree' => 1,
                    'title' => '휴면 계정 전환 안내',
                    'contents' => $templateInactivatePriorNotice,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now()
                ],
                [
                    'user_id' => $manager->id,
                    'code' => 'Users.AutoWithdrawalPriorNotice',
                    'name' => '[회원] 탈퇴처리 및 개인정보 파기 안내',
                    'enable' => 1,
                    'ignore_agree' => 1,
                    'title' => '탈퇴처리 및 개인정보 파기 안내',
                    'contents' => $templateWithdrawalPriorNotice,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now()
                ]
            ]
        );
    }
}
