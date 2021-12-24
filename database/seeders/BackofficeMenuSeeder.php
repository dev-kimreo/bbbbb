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
            ],
            [
                'name' => '회원관리',
            ],
            [
                'name' => '라이브러리',
                'child' => [
                    ['name' => '이메일 관리'],
                    ['name' => '위젯 관리'],
                    ['name' => '아이콘 그룹 관리'],
                    ['name' => '폰트 목록 관리'],

                ]
            ],
            [
                'name' => '파트너센터',
                'child' =>
                    [
                        ['name' => '파트너 회원 관리'],
                        ['name' => '심사 관리'],
                        ['name' => '테마 상품'],
//                            ['name' => '플러그인 상품'],
                        ['name' => '컴포넌트 관리'],
                        ['name' => '기본 페이지 관리'],
                    ]
            ],
//                [
//                    'name' => '스토어'
//                ],
            [
                'name' => '전시관리',
                'child' => [
                    ['name' => '팝업/배너 관리'],
                    ['name' => '이용약관'],
                    ['name' => '개인정보처리방침'],
                ]
            ],
            [
                'name' => '게시판관리',
                'child' => [
                    ['name' => '게시판 목록'],
                    ['name' => '게시글 목록'],
                    ['name' => '1:1 문의 목록'],
                ]
            ],
//                [
//                    'name' => '결제내역'
//                ],
            [
                'name' => '통계'
            ],
            [
                'name' => '시스템관리',
                'child' => [
                    ['name' => '알림센터'],
                    ['name' => '관리자 설정'],
                    ['name' => '툴팁 관리'],
                    ['name' => '현지화 언어 관리'],
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
