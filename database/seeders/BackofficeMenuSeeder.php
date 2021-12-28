<?php

namespace Database\Seeders;

use App\Models\BackofficeMenu;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class BackofficeMenuSeeder extends Seeder
{
    public int $depth = 1;
    public array $parent = [0];
    public int $last = 1;

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $menus = [
            [
                'name' => '대시보드',
                'key' => 'dashboard',
            ],
            [
                'name' => '회원관리',
                'key' => 'user'
            ],
            [
                'name' => '라이브러리',
                'key' => 'library',
                'child' => [
                    ['name' => '이메일 관리', 'key' => 'email'],
                    ['name' => '위젯 관리', 'key' => 'widget'],
                    ['name' => '아이콘 그룹 관리', 'key' => 'icon-group'],
                    ['name' => '폰트 목록 관리', 'key' => 'font'],

                ]
            ],
            [
                'name' => '파트너센터',
                'key' => 'partner-center',
                'child' =>
                    [
                        ['name' => '파트너 회원 관리', 'key' => 'partner-user'],
                        ['name' => '심사 관리', 'key' => 'audit'],
                        ['name' => '테마 상품', 'key' => 'theme'],
//                            ['name' => '플러그인 상품', 'key' => 'plugin'],
                        ['name' => '컴포넌트 관리', 'key' => 'component'],
                        ['name' => '기본 페이지 관리', 'key' => 'default-page'],
                    ]
            ],
//                [
//                    'name' => '스토어',
//                    'key' => 'store'
//                ],
            [
                'name' => '전시관리',
                'key' => 'display',
                'child' => [
                    ['name' => '팝업/배너 관리', 'key' => 'popup-banner'],
                    ['name' => '이용약관', 'key' => 'user-agreement'],
                    ['name' => '개인정보처리방침', 'key' => 'privacy-policy'],
                ]
            ],
            [
                'name' => '게시판관리',
                'key' => 'board',
                'child' => [
                    ['name' => '게시판 목록', 'key' => 'board-list'],
                    ['name' => '게시글 목록', 'key' => 'post-list'],
                    ['name' => '1:1 문의 목록', 'key' => 'inquiry-list'],
                ]
            ],
//                [
//                    'name' => '결제내역'
//                    'key'= > 'payment-history',
//                ],
            [
                'name' => '통계',
                'key' => 'statistics'
            ],
            [
                'name' => '시스템관리',
                'key' => 'system',
                'child' => [
                    ['name' => '알림센터', 'key' => 'notification'],
                    ['name' => '관리자 설정', 'key' => 'manager'],
                    ['name' => '툴팁 관리', 'key' => 'tooltip'],
                    ['name' => '현지화 언어 관리', 'key' => 'localization'],
//                        ['name' => '솔루션 연동'],
                ]
            ]
        ];

        $this->createMenu($menus);
    }

    protected function createMenu($menu)
    {
        foreach ($menu as $k => $arr) {
            if (isset($arr['child'])) {
                $this->last = 0;
            } else {
                $this->last = 1;
            }

            $res = BackofficeMenu::create([
                'name' => $arr['name'],
                'key' => $arr['key'],
                'depth' => $this->depth,
                'parent' => $this->parent[count($this->parent)-1],
                'last' => $this->last,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]);

            if (isset($arr['child'])) {
                array_push($this->parent, $res->getAttribute('id'));
                $this->depth++;
                $this->createMenu($arr['child']);
                $this->depth--;
                array_pop($this->parent);
            }
        }
    }

}
