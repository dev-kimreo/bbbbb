<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;
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
                'messages',
                'auth.incorrect_timeout',
                '잘못된 인증방식이거나 token의 유효시간이 지났습니다.',
                'Either incorrect information or the token expiration time has expired.'
            ],
            [
                'messages',
                'user.username.incorrect',
                '이메일 주소가 잘못되었거나, 아직 가입이 진행되지 않았습니다.',
                'Incorrect email address.'
            ],
            [
                'messages',
                'user.password.incorrect',
                '비밀번호가 일치하지 않습니다.',
                'Incorrect password.'
            ],
            [
                'messages',
                'user.password.validation.characters',
                '비밀번호에는 특수문자, 문자, 숫자가 모두 포함되어 있어야 합니다.',
                'The Password must have alphabets, numbers and special characters.'
            ],
            [
                'messages',
                'user.password.validation.repetition',
                '비밀번호에는 같은 문자 또는 숫자를 4번 반복하여 사용할 수 없습니다.',
                'The password must not repeat the same character 4 times.'
            ],
            [
                'messages',
                'user.password.validation.used_space',
                '비밀번호에는 공백문자를 사용할 수 없습니다.',
                'Spaces cannot be used in the password.'
            ],
            [
                'messages',
                'user.password.validation.matched_email',
                '비밀번호에는 아이디(이메일 주소)와 일치하는 단어를 사용할 수 없습니다.',
                'Passwords cannot contain words that match your email address.'
            ],
            [
                'messages',
                'email.too_many_send',
                '짧은 시간내에 잦은 요청으로 인해 재발송 불가 합니다.',
                'Too many attempting to send mail.'
            ],
            [
                'messages',
                'email.already_verified',
                '이미 인증된 회원입니다.',
                'Already verified.'
            ],
            [
                'messages',
                'email.incorrect',
                '잘못된 인증 방식입니다.',
                'Incorrect verifying.'
            ],
            [
                'messages',
                'board.disable.not_permitted',
                '게시판을 추가, 수정 및 삭제할 권한이 없습니다.',
                'Not permitted to create, edit or remove boards.'
            ],
            [
                'messages',
                'reply.disable.board_option',
                '댓글을 작성할 수 없도록 설정된 게시판입니다.',
                'Can\'t writing a reply on this board.'
            ],
            [
                'messages',
                'reply.disable.post_hidden',
                '숨겨진 게시글에는 댓글을 작성할 수 없습니다.',
                'Can\'t writing a reply because this post is hidden.'
            ],
            [
                'messages',
                'reply.disable.writer_only',
                '댓글의 수정이나 삭제는 작성자만 할 수 있습니다.',
                'Can\'t writing a reply because you are not writer.'
            ]
        ];

        Schema::disableForeignKeyConstraints();
        Translation::truncate();
        TranslationContent::truncate();

        foreach($words as $v) {
            $word = new Translation;
            $word->type = $v[0];
            $word->code = $v[1];
            $word->explanation = $v[2];
            $word->save();

            $lang = new TranslationContent;
            $lang->lang = 'ko';
            $lang->value = $v[2];
            $word->translationContent()->save($lang);

            $lang = new TranslationContent;
            $lang->lang = 'en';
            $lang->value = $v[3];
            $word->translationContent()->save($lang);
        }
    }
}
