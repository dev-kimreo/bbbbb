<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BoardsSeeder extends Seeder
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
                "name" => '공지사항',
                "type" => 'notice',
                "options" => '{"board":"manager","boardReply":"0","thema":"boardDefaultThema","thumbnail":"0","boardStatus":"0","secret":"0","reply":"0","editor":"all","attach":"0"}'
            ],
            [
                "name" => '시작하기',
                "type" => 'guide',
                "options" => '{"board":"all","boardReply":"0","thema":"boardThumbnailThema","thumbnail":"1","boardStatus":"0","secret":"0","reply":"0","editor":"all","attach":"0"}'
            ],
        ];
        //
        foreach ($insArrs as $v) {
            DB::table('boards')->insert($v);
        }
    }
}
