<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BoardOptionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $insArrs = [
            [
                "name" => '작성 권한',
                "type" => 'board',
                "default" => 'all',
                "options" => json_encode([
                    ["value" => "all", "comment" => "모두 작성 가능"],
                    ["value" => "manager", "comment" => "운영진만 작성 가능"],
                    ["value" => "member", "comment" => "회원만 작성 가능"]
                ])
            ],
            [
                "name" => '게시판 테마',
                "type" => 'theme',
                "default" => 'boardDefaultTheme',
                "options" => json_encode([
                ])
            ],
            [
                "name" => '섬네일 사용',
                "type" => 'thumbnail',
                "default" => '0',
                "options" => json_encode([
                    ["value" => "0", "comment" => "사용 안함"],
                    ["value" => "1", "comment" => "사용 함"]
                ])
            ],
            [
                "name" => '댓글 사용',
                "type" => 'reply',
                "default" => '0',
                "options" => json_encode([
                    ["value" => "0", "comment" => "사용 안함"],
                    ["value" => "1", "comment" => "사용 함"]
                ])
            ],
            [
                "name" => '에디터',
                "type" => 'editor',
                "default" => 'all',
                "options" => json_encode([
                    ["value" => "all", "comment" => "모두 사용"],
                    ["value" => "tinyMCE", "comment" => "tiny에디터 사용"],
                    ["value" => "markdown", "comment" => "마크다운 사용"]
                ])
            ],
            [
                "name" => '파일 첨부',
                "type" => 'attach',
                "default" => '0',
                "options" => json_encode([
                    ["value" => "0", "comment" => "사용 안함"],
                    ["value" => "1", "comment" => "사용 함"]
                ])
            ],
            [
                "name" => '파일 첨부 갯수 제한',
                "type" => 'attachLimit',
                "default" => '10',
                "options" => json_encode([
                ])
            ],
            [
                "name" => '등록일 노출',
                "type" => 'createdAt',
                "default" => '1',
                "options" => json_encode([
                    ["value" => "0", "comment" => "사용 안함"],
                    ["value" => "1", "comment" => "사용 함"]
                ])
            ]
        ];
        //
        foreach ($insArrs as $v) {
            DB::table('board_options')->insert($v);
        }
    }
}
