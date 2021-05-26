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
                "options" => '{"board":"manager","theme":"boardDefaultTheme","thumbnail":"0","reply":"0","editor":"all","attach":"0","attachLimit":10}'
            ],
            [
                "name" => '시작하기',
                "options" => '{"board":"all","theme":"boardThumbnailTheme","thumbnail":"1","reply":"0","editor":"all","attach":"0","attachLimit":10}'
            ],
        ];
        //
        foreach ($insArrs as $v) {
            DB::table('boards')->insert($v);
        }
    }
}
