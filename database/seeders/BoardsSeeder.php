<?php

namespace Database\Seeders;

use App\Models\Board;
use App\Models\Manager;
use App\Models\Post;
use App\Models\Reply;
use App\Models\Users\User;
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
        Board::unsetEventDispatcher();
        Post::unsetEventDispatcher();

        $manager = Manager::offset(1)->limit(2)->get();

        $insArrs = [
            [
                "name" => '공지사항',
                "enable" => 0,
                "user_id" => $manager->get(0)->id,
                "options" => [
                    "board" => "manager",
                    "theme" => "boardDefaultTheme",
                    "thumbnail" => 0,
                    "reply" => 0,
                    "editor" => "all",
                    "attach" => 0,
                    "attachLimit" => 10
                ]
            ],
            [
                "name" => '시작하기',
                "enable" => 1,
                "user_id" => $manager->get(0)->id,
                "options" => [
                    "board" => "all",
                    "theme" => "boardDefaultTheme",
                    "thumbnail" => 1,
                    "reply" => 0,
                    "editor" => "all",
                    "attach" => 0,
                    "attachLimit" => 10
                ]
            ],
            [
                "name" => '자유게시판',
                "enable" => 1,
                "user_id" => $manager->get(0)->id,
                "options" => [
                    "board" => "all",
                    "theme" => "boardDefaultTheme",
                    "thumbnail" => 0,
                    "reply" => 1,
                    "editor" => "all",
                    "attach" => 1,
                    "attachLimit" => 10
                ]
            ],
            [
                "name" => 'empty',
                "enable" => 1,
                "user_id" => $manager->get(0)->id,
                "options" => [
                    "board" => "all",
                    "theme" => "boardDefaultTheme",
                    "thumbnail" => 1,
                    "reply" => 0,
                    "editor" => "all",
                    "attach" => 0,
                    "attachLimit" => 10
                ]
            ],
        ];

        //
        foreach ($insArrs as $v) {
            $board = new Board;
            $board->fill($v);
            $board->save();

            if ($v['name'] == 'empty') {
                continue;
            }

            $posts = [];
            $posts = array_merge(
                        $posts,
                        Post::factory()->count(10)->make(['user_id' => $manager->get(0)->id])->toArray(),
                        Post::factory()->count(10)->make(['user_id' => $manager->get(1)->id])->toArray()
                    );

            $board->posts()->createMany($posts);

            $board->posts()->each(function ($m) use ($manager) {
                Reply::factory()->count(10)->create(['post_id' => $m->id, 'user_id' => $manager->get(0)->id]);
                Reply::factory()->count(10)->create(['post_id' => $m->id, 'user_id' => $manager->get(1)->id]);

            });
        }


    }
}
