<?php

namespace Database\Seeders;

use App\Models\Exception;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;
use App\Models\Translation;
use App\Models\TranslationContent;

class ExceptionSeeder extends Seeder
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
                'exception',
                'common.not_found',
                '요청한 데이터를 찾을 수 없습니다.',
                'Not found.'
            ],
            [
                'exception',
                'common.already_exists',
                '이미 존재하는 데이터입니다.',
                'Already exists.'
            ],
            [
                'exception',
                'common.unauthorized',
                '요청 권한이 없습니다.',
                'Unauthorized.'
            ],
            [
                'exception',
                'common.bad_request',
                '잘못된 요청입니다.',
                'Bad Request.'
            ],
            [
                'exception',
                'common.forbidden',
                '요청 권한이 없습니다.',
                'Forbidden.'
            ],
            [
                'exception',
                'common.not_found_model',
                '데이터베이스 모델을 찾을 수 없습니다.',
                'Not found database model.'
            ],
            [
                'exception',
                'common.pagination.out_of_bounds',
                '표시할 수 있는 총 페이지 수를 넘어섰습니다.',
                'The page is out of bounds.'
            ],
            [
                'exception',
                'common.wrong_language_code',
                '올바른 ISO 639-1 코드가 아닙니다.',
                'It is not an ISO 639-1 code.'
            ],
            [
                'exception',
                'common.within_days_of_other_date',
                '다른 날짜와의 차이가 :day일 이내이여야 합니다.',
                '다른 날짜와의 차이가 :day일 이내이여야 합니다.'
            ],
            [
                'exception',
                'common.not_matched_target_column',
                ':targetColumnName 값이 일치하지 않습니다.',
                ':targetColumnName 값이 일치하지 않습니다.'
            ],
            [
                'exception',
                'auth.incorrect_timeout',
                '잘못된 인증방식이거나 token의 유효시간이 지났습니다.',
                'Either incorrect information or the token expiration time has expired.'
            ],
            [
                'exception',
                'user.registered',
                '가입이 완료되었습니다.',
                'successfully registered.'
            ],
            [
                'exception',
                'user.username.incorrect',
                '이메일 주소가 잘못되었거나, 아직 가입이 진행되지 않았습니다.',
                'Incorrect email address.'
            ],
            [
                'exception',
                'user.password.incorrect',
                '비밀번호가 일치하지 않습니다.',
                'Incorrect password.'
            ],
            [
                'exception',
                'user.password.reuse',
                '입력하신 비밀번호는 변경 전과 동일하여 다시 사용할 수 없습니다.',
                'Can\'t reuse the password you used before the change.'
            ],
            [
                'exception',
                'user.password.validation.characters',
                '비밀번호에는 특수문자, 문자, 숫자가 모두 포함되어 있어야 합니다.',
                'The Password must have alphabets, numbers and special characters.'
            ],
            [
                'exception',
                'user.password.validation.repetition',
                '비밀번호에는 같은 문자 또는 숫자를 4번 반복하여 사용할 수 없습니다.',
                'The password must not repeat the same character 4 times.'
            ],
            [
                'exception',
                'user.password.validation.used_space',
                '비밀번호에는 공백문자를 사용할 수 없습니다.',
                'Spaces cannot be used in the password.'
            ],
            [
                'exception',
                'user.password.validation.matched_email',
                '비밀번호에는 아이디(이메일 주소)와 일치하는 단어를 사용할 수 없습니다.',
                'Passwords cannot contain words that match your email address.'
            ],
            [
                'exception',
                'user.inactive',
                '휴면회원 입니다. 계정 활성화 후 이용해주세요.',
                '휴면회원 입니다. 계정 활성화 후 이용해주세요.'
            ],
            [
                'exception',
                'user.not_associative',
                '준회원만 사용할 수 있습니다.',
                'Only for associative users.'
            ],
            [
                'exception',
                'email.too_many_send',
                '짧은 시간내에 잦은 요청으로 인해 재발송 불가 합니다.',
                'Too many attempting to send mail.'
            ],
            [
                'exception',
                'email.already_verified',
                '이미 인증된 회원입니다.',
                'Already verified.'
            ],
            [
                'exception',
                'email.incorrect',
                '잘못된 인증 방식입니다.',
                'Incorrect verifying.'
            ],
            [
                'exception',
                'email.verification_resend',
                '인증메일을 재발송 하였습니다.',
                'A verification mail has been sent again.'
            ],
            [
                'exception',
                'email.failed_validation_signature',
                '메일 인증키가 유효하지 않습니다.',
                'Wring validation key.'
            ],
            [
                'exception',
                'email.not_found_sign_code',
                '발급된 적이 없거나 만료된 메일인증키입니다.',
                'The validation key is not found.'
            ],
            [
                'exception',
                'board.disable.not_permitted',
                '게시판을 추가, 수정 및 삭제할 권한이 없습니다.',
                'Not permitted to create, edit or remove boards.'
            ],
            [
                'exception',
                'board.option.disable.unknown_key',
                '입력할 수 있는 게시판 옵션이 아닙니다.',
                'Can\'t use the key as board\'s option.'
            ],
            [
                'exception',
                'board.option.disable.wrong_value',
                '입력한 값은 해당 게시판 옵션의 값으로 사용할 수 없습니다.',
                'Can\'t use the value as board\'s option.'
            ],
            [
                'exception',
                'board.delete.disable.exists_post',
                '등록된 게시글이 존재하여 삭제할 수 없습니다.',
                '등록된 게시글이 존재하여 삭제할 수 없습니다.'
            ],
            [
                'exception',
                'post.disable.hidden',
                '숨겨진 게시글입니다.',
                'Can\'t read because post is hidden.'
            ],
            [
                'exception',
                'reply.disable.board_option',
                '댓글을 작성할 수 없도록 설정된 게시판입니다.',
                'Can\'t writing a reply on this board.'
            ],
            [
                'exception',
                'reply.disable.post_hidden',
                '숨겨진 게시글에는 댓글을 작성할 수 없습니다.',
                'Can\'t writing a reply because this post is hidden.'
            ],
            [
                'exception',
                'reply.disable.writer_only',
                '댓글의 수정이나 삭제는 작성자만 할 수 있습니다.',
                'Can\'t writing a reply because you are not writer.'
            ],
            [
                'exception',
                'inquiry.disable.writer_only',
                '내가 작성하지 않은 1:1 상담은 열람 또는 수정할 수 없습니다.',
                'Can\'t read because you are not writer.'
            ],
            [
                'exception',
                'inquiry.answer.disable.already_exists',
                '이미 답변이 완료된 1:1 상담입니다.',
                'Can\'t write answer because the inquiry already has an answer.'
            ],
            [
                'exception',
                'attach.disable.upload',
                '업로드 기능을 사용할 수 없습니다.',
                '업로드 기능을 사용할 수 없습니다.'
            ],
            [
                'exception',
                'attach.over.limit',
                '업로드 제한 갯수를 초과하였습니다.',
                '업로드 제한 갯수를 초과하였습니다.'
            ],
            [
                'exception',
                'attach.over.upload_limit',
                '업로드된 파일이 1회 업로드 제한 용량을 초과하였습니다.',
                '업로드된 파일이 1회 업로드 제한 용량을 초과하였습니다.'
            ],
            [
                'exception',
                'attach.over.storage_limit',
                '업로드 사용량을 초과하였습니다.',
                '업로드 사용량을 초과하였습니다.'
            ],
            [
                'exception',
                'menu.delete.disable.exists_children',
                '하위 메뉴가 존재하여 삭제할 수 없습니다.',
                '하위 메뉴가 존재하여 삭제할 수 없습니다.'
            ],
            [
                'exception',
                'menu.permission.only.last',
                '마지막 메뉴만이 권한을 가질 수 있습니다.',
                '마지막 메뉴만이 권한을 가질 수 있습니다.'
            ],
            [
                'exception',
                'authority.delete.disable.exists_manager',
                '현재 사용중인 관리자가 존재하여 삭제할 수 없습니다.',
                '현재 사용중인 관리자가 존재하여 삭제할 수 없습니다.'
            ],
            [
                'exception',
                'terms.disable.modify.over.started_date',
                '전시 시작일이 지나 수정/삭제가 불가능 합니다.',
                '전시 시작일이 지나 수정/삭제가 불가능 합니다.'
            ],
            [
                'exception',
                'widget_usage.disable.writer_only',
                '나의 소유가 아닌 위젯 사용내역은 수정할 수 없습니다.',
                'Can\'t read because you are not writer.'
            ],
            [
                'exception',
                'theme.disable.already_exists',
                '이미 해당 솔루션 테마가 존재하여 추가할 수 없습니다.',
                '이미 해당 솔루션 테마가 존재하여 추가할 수 없습니다.'
            ],
            [
                'exception',
                'supported_editable_page.disable.already_exists',
                '이미 해당 에디터 지원 페이지가 존재하여 추가할 수 없습니다.',
                '이미 해당 에디터 지원 페이지가 존재하여 추가할 수 없습니다.'
            ],
            [
                'exception',
                'supported_editable_page.disable.add_to_selected_theme',
                '해당 에디터 지원 페이지는 선택한 테마에 추가할 수 없습니다.',
                '해당 에디터 지원 페이지는 선택한 테마에 추가할 수 없습니다.'
            ],
            [
                'exception',
                'editable_page_layout.disable.already_exists',
                '이미 해당 에디터 지원 페이지 레이아웃이 존재하여 추가할 수 없습니다.',
                '이미 해당 에디터 지원 페이지 레이아웃이 존재하여 추가할 수 없습니다.'
            ],
            [
                'exception',
                'component.disable.selected_pages_for_common_solution',
                '공통 솔루션 선택 시 사용 페이지 옵션의 페이지 선택은 사용 할 수 없습니다.',
                '공통 솔루션 선택 시 사용 페이지 옵션의 페이지 선택은 사용 할 수 없습니다.'
            ],
            [
                'exception',
                'component.disable.modify.registered',
                '등록 완료 된 컴포넌트는 수정 할 수 없습니다.',
                '등록 완료 된 컴포넌트는 수정 할 수 없습니다.'
            ],
            [
                'exception',
                'component.disable.destroy.registered',
                '등록 완료 된 컴포넌트는 삭제 할 수 없습니다.',
                '등록 완료 된 컴포넌트는 삭제 할 수 없습니다.'
            ],
            [
                'exception',
                'component_version.disable.create.limited_count_over',
                '등록 가능한 컴포넌트 버전의 갯수를 초과하였습니다.',
                '등록 가능한 컴포넌트 버전의 갯수를 초과하였습니다.'
            ],
            [
                'exception',
                'component_version.disable.destroy.in_use',
                '사용 중인 컴포넌트 버전은 삭제 할 수 없습니다.',
                '사용 중인 컴포넌트 버전은 삭제 할 수 없습니다.'
            ],
            [
                'exception',
                'component_type.disable.destroy.in_use',
                '사용 중인 컴포넌트 유형은 삭제 할 수 없습니다.',
                '사용 중인 컴포넌트 유형은 삭제 할 수 없습니다.'
            ],
            [
                'exception',
                'theme.solution.not_found',
                '테마의 솔루션 정보가 잘못되었습니다.',
                'Wrong solution information'
            ],
            [
                'exception',
                'theme.solution.not_matched',
                '테마의 솔루션이 회원 솔루션 정보와 일치하지 않습니다.',
                'Selected not matched solution to theme'
            ],
            [
                'exception',
                'theme.export.ftp.host',
                '솔루션 FTP 접속정보가 잘못되었습니다.',
                'Wrong host or port for connect FTP'
            ],
            [
                'exception',
                'theme.export.ftp.root',
                '솔루션 FTP 접속 디렉토리가 잘못되었습니다.',
                'Wrong directory path for connect FTP'
            ],
            [
                'exception',
                'theme.export.ftp.login',
                '솔루션 아이디 또는 FTP 비밀번호가 잘못되었습니다.',
                'Wrong user name or password for connect FTP'
            ],

            /**
             * reo 에러메시지 테스트간 필요한 seeder 데이터
             * Start
             */
            [
                'exception',
                'system.http.404.board',
                '존재하지 않는 :word.board.board입니다.',
                ':word.board.board is not exists'
            ],
            [
                'exception',
                'system.http.404.post',
                '존재하지 않는 :word.post.post입니다.',
                ':word.post.post is not exists'
            ],
            [
                'exception',
                'validation.required.post.title',
                ':word.post.title을 필수로 입력해주세요.',
                ':word.post.title is necessary'
            ],
            [
                'exception',
                'validation.required.post.content',
                ':word.post.content을 필수로 입력해주세요.',
                ':word.post.content is necessary'
            ],

            /**
             * End
             */

        ];

        // Truncate tables
        if (app()->environment() == 'local') {
            Schema::disableForeignKeyConstraints();
            Exception::truncate();
            Translation::truncate();
            TranslationContent::truncate();
        }

        // Insert data
        foreach ($words as $v) {
            $exp = Exception::create([
                'code' => $v[1],
                'title' => $v[2]
            ]);

            $word = new Translation;
            $word->linkable_type = $v[0];
            $word->linkable_id = $exp->id;
            $word->save();

            $lang = new TranslationContent;
            $lang->lang = 'ko';
            $lang->value = $v[2];
            $word->translationContents()->save($lang);

            $lang = new TranslationContent;
            $lang->lang = 'en';
            $lang->value = $v[3];
            $word->translationContents()->save($lang);
        }
    }
}
