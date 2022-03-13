<?php

namespace Database\Seeders;

use App\Models\Attach\AttachFile;
use App\Models\Exhibitions\Banner;
use App\Models\Exhibitions\BannerDeviceContent;
use App\Models\Exhibitions\Exhibition;
use App\Models\Exhibitions\ExhibitionCategory;
use App\Models\Exhibitions\ExhibitionTargetUser;
use App\Models\Users\User;
use App\Models\Users\UserPrivacyActive;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ExhibitionsSeeder extends Seeder
{

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        ExhibitionCategory::factory()->count(10)->create();

        for ($i = 0; $i < 10; $i++) {
            $banner = Banner::factory()
                ->has(
                    Exhibition::factory()->for(
                        ExhibitionCategory::all()->random(1)->first(),
                        'category'
                    )
                )->has(
                    BannerDeviceContent::factory()->has(
                        AttachFile::factory()->for(
                            User::all()->random(1)->first(),
                            'uploader'
                        ),
                        'attachFile'
                    ),
                    'contents'
                )
                ->create(['user_id' => User::all()->random(1)->first()]);

            if ($banner->exhibition()->first()->target_opt == 'designate') {
                $users = User::selectRaw('id as user_id')->get();
                $count = rand(1, min(10, $users->count()));
                $targets = $users->random($count)->toArray();

                $banner->exhibition->targetUsers()->createMany(
                    $targets
                );
            }
        }


//        for ($i = 0; $i < 30; $i++) {

//            Banner::factory()
//                ->has(
//                    Exhibition::factory()->for(
//                        ExhibitionCategory::factory()->create(),
//                        'category'
//                    )->has(ExhibitionTargetUser::factory()->count(10)->for(
//                        User::factory()->has(
//                            UserPrivacyActive::factory(), 'privacy'
//                        )->create()
//                    ), 'targetUsers')
//                )
//                ->for(User::factory()->has(
//                    UserPrivacyActive::factory(), 'privacy'
//                )->create(), 'creator')
//                ->has(
//                    BannerDeviceContent::factory()->has(
//                        AttachFile::factory()->for(
//                            User::factory()->has(
//                                UserPrivacyActive::factory(), 'privacy'
//                            )->create(),
//                            'uploader'
//                        ),
//                        'attachFile'
//                    ),
//                    'contents'
//                )->count(1)->create();
////        }

//        $manager = Manager::limit(2)->get();
//
//        $insArrs = [
//            [
//                "name" => '공지사항',
//                "enable" => 0,
//                "user_id" => $manager->get(0)->id,
//                "options" => [
//                    "board" => "manager",
//                    "theme" => "boardDefaultTheme",
//                    "thumbnail" => 0,
//                    "reply" => 0,
//                    "editor" => "all",
//                    "attach" => 0,
//                    "attachLimit" => 10
//                ]
//            ],
//            [
//                "name" => '시작하기',
//                "enable" => 1,
//                "user_id" => $manager->get(0)->id,
//                "options" => [
//                    "board" => "all",
//                    "theme" => "boardDefaultTheme",
//                    "thumbnail" => 1,
//                    "reply" => 0,
//                    "editor" => "all",
//                    "attach" => 0,
//                    "attachLimit" => 10
//                ]
//            ],
//            [
//                "name" => '자유게시판',
//                "enable" => 1,
//                "user_id" => $manager->get(0)->id,
//                "options" => [
//                    "board" => "all",
//                    "theme" => "boardDefaultTheme",
//                    "thumbnail" => 0,
//                    "reply" => 1,
//                    "editor" => "all",
//                    "attach" => 1,
//                    "attachLimit" => 10
//                ]
//            ],
//            [
//                "name" => 'empty',
//                "enable" => 1,
//                "user_id" => $manager->get(0)->id,
//                "options" => [
//                    "board" => "all",
//                    "theme" => "boardDefaultTheme",
//                    "thumbnail" => 1,
//                    "reply" => 0,
//                    "editor" => "all",
//                    "attach" => 0,
//                    "attachLimit" => 10
//                ]
//            ],
//        ];
//
//        //
//        foreach ($insArrs as $v) {
//            $board = new Board;
//            $board->fill($v);
//            $board->save();
//
//            if ($v['name'] == 'empty') {
//                continue;
//            }
//
//            $posts = [];
//            $posts = array_merge(
//                        $posts,
//                        Post::factory()->count(10)->make(['user_id' => $manager->get(0)->id])->toArray(),
//                        Post::factory()->count(10)->make(['user_id' => $manager->get(1)->id])->toArray()
//                    );
//
//            $board->posts()->createMany($posts);
//
//            $board->posts()->each(function ($m) use ($manager) {
//                Reply::factory()->count(10)->create(['post_id' => $m->id, 'user_id' => $manager->get(0)->id]);
//                Reply::factory()->count(10)->create(['post_id' => $m->id, 'user_id' => $manager->get(1)->id]);
//
//            });
//        }


    }
}
