<?php

namespace Database\Seeders;

use App\Models\Word;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;
use App\Models\Translation;
use App\Models\TranslationContent;

class WordSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $words = [
            /**
             * reo 에러메시지 테스트간 필요한 seeder 데이터
             * Start
             */
            [
                'word',
                '_board',
                '게시판',
                'Board'
            ],
            [
                'word',
                '_post',
                '게시글',
                'Post'
            ],
            [
                'word',
                'post.title',
                '게시글 제목',
                'Post Title'
            ],
            [
                'word',
                'post.content',
                '게시글 내용',
                'Post Content'
            ],
            /**
             * End
             */
        ];

        // Truncate tables
        if (app()->environment() == 'local') {
            Schema::disableForeignKeyConstraints();
            Word::truncate();
//            Translation::truncate();
//            TranslationContent::truncate();
        }

        // Insert data
        foreach ($words as $v) {
            $exp = Word::create([
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
