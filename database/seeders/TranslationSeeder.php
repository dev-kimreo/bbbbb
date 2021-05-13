<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Translation;
use App\Models\TranslationContent;

class TranslationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $words = [
            [
                'messages.auth.incorrect_timeout',
                '잘못된 인증방식이거나 token의 유효시간이 지났습니다.',
                'Either incorrect information or the token expiration time has expired.'
            ],
            [
                'messages.board.disable_reply',
                '댓글을 작성할 수 없는 게시판입니다.',
                'Can\'t writing a reply on this board.'
            ],
            [
                'messages.email.too_many_send',
                '짧은 시간내에 잦은 요청으로 인해 재발송 불가 합니다.',
                'Too many attempting to send mail.'
            ],
            [
                'messages.email.already_verified',
                '이미 인증된 회원입니다.',
                'Already verified.'
            ],
            [
                'messages.email.incorrect',
                '잘못된 인증 방식입니다.',
                'Incorrect verifying.'
            ]
        ];

        foreach($words as $v) {
            $word = new Translation;
            $word->code = $v[0];
            $word->explanation = $v[1];
            $word->save();

            $lang = new TranslationContent;
            $lang->lang = 'ko';
            $lang->value = $v[1];
            $word->translationContent()->save($lang);

            $lang = new TranslationContent;
            $lang->lang = 'en';
            $lang->value = $v[2];
            $word->translationContent()->save($lang);
        }
    }
}
