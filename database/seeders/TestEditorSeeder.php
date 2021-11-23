<?php

namespace Database\Seeders;

use App\Models\Components\Component;
use App\Models\Components\ComponentOption;
use App\Models\Components\ComponentType;
use App\Models\Components\ComponentTypeProperty;
use App\Models\Components\ComponentVersion;
use App\Models\EditablePages\EditablePage;
use App\Models\EditablePages\EditablePageLayout;
use App\Models\LinkedComponents\LinkedComponent;
use App\Models\LinkedComponents\LinkedComponentGroup;
use App\Models\LinkedComponents\LinkedComponentOption;
use App\Models\Manager;
use App\Models\Solution;
use App\Models\SupportedEditablePage;
use App\Models\Themes\Theme;
use App\Models\Themes\ThemeProduct;
use App\Models\Themes\ThemeProductInformation;
use App\Models\Users\User;
use App\Models\Users\UserPartner;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Testing\WithFaker;

class TestEditorSeeder extends Seeder
{
    use WithFaker;

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
//        $this->setUpFaker();

        // 지원 가능한 에디터 지원 페이지 추가
        if (!count(Solution::first()->supportedEditablePage)) {
            Solution::all()->each(function ($item) {
                $item->supportedEditablePage()->createMany(
                    [
                        [
                            'name' => '메인',
                            'file_name' => 'main.html'
                        ],
                        [

                            'name' => '목록',
                            'file_name' => 'list.html'
                        ],
                    ]
                );
            });
        }

        // 컴포넌트 텍스트 유형 추가
        $componentType = ComponentType::firstOrCreate(
            ['code' => 'text'],
            [
                'name' => '텍스트형',
                'isPlural' => false
            ]
        );

        // 컴포넌트 텍스트 옵션 유형 속성 추가
        ComponentTypeProperty::firstOrCreate(
            ['component_type_id' => $componentType->id],
            [
                'type' => 'text',
                'hasOption' => false,
                'hasDefault' => true,
                'default' => '텍스트필드'
            ]
        );

        $componentName = '4단 배너';
        $html = '
            <ul>
                <li><a><img /></a></li>
                <li><a><img /></a></li>
                <li><a><img /></a></li>
                <li><a><img /></a></li>
            </ul>
        ';
        $css = '
            ul {
                display:gird;
                grid-template-columns:2;
                grid-template-rows:2;
                width:100%;
                height:400px;
                margin:0;
                padding:0;
            }
            li {
                margin:0;
                padding:0;
                list-style-type:none;
            }
        ';
        $script = '
            let anchors = document.querySelectorAll("a");

            for(let i=0; i<anchors.length; i++) {
                anchors[i].href = compOpt["url" + i];
                anchors[i].querySelector("img").src = compOpt["img" + i];
            }
        ';

        // 컴포넌트
        $component = Component::factory()->for(
            UserPartner::first(),
            'creator'
        )->for(
            Solution::first(),
            'solution'
        )->has(
            ComponentVersion::factory()->state([
                'usable' => true,
                'template' => $html,
                'style' => $css,
                'script' => $script
            ])->has(
                ComponentOption::factory()
                    ->state(
                        [
                            'name' => '1번째 링크 URL',
                            'key' => 'url0',
                            'help' => '1번째 이미지를 클릭했을 때 표시될 링크입니다.',
                            'default' => 'https://en.wikipedia.org/wiki/Strawberry'
                        ]
                    )->for(ComponentType::first(), 'type'),
                'option'
            )->has(
                ComponentOption::factory()
                    ->state(
                        [
                            'name' => '1번째 이미지 URL',
                            'key' => 'img0',
                            'help' => '1번째 이미지의 URL입니다.',
                            'default' => 'https://static.libertyprim.com/files/familles/fraise-large.jpg?1569271765'
                        ]
                    )->for(ComponentType::first(), 'type'),
                'option'
            )->has(
                ComponentOption::factory()
                    ->state(
                        [
                            'name' => '2번째 링크 URL',
                            'key' => 'url1',
                            'help' => '첫 번째 이미지를 클릭했을 때 표시될 링크입니다.',
                            'default' => 'https://en.wikipedia.org/wiki/Melon'
                        ]
                    )->for(ComponentType::first(), 'type'),
                'option'
            )->has(
                ComponentOption::factory()
                    ->state(
                        [
                            'name' => '2번째 이미지 URL',
                            'key' => 'img1',
                            'help' => '2번째 이미지의 URL입니다.',
                            'default' => 'https://english.ibarakiguide.org/wp-content/uploads/2020/06/melonseason2.jpg',
                        ]
                    )->for(ComponentType::first(), 'type'),
                'option'
            )->has(
                ComponentOption::factory()
                    ->state(
                        [
                            'name' => '3번째 링크 URL',
                            'key' => 'url2',
                            'help' => '3번째 이미지를 클릭했을 때 표시될 링크입니다.',
                            'default' => 'https://en.wikipedia.org/wiki/Grape'
                        ]
                    )->for(ComponentType::first(), 'type'),
                'option'
            )->has(
                ComponentOption::factory()
                    ->state(
                        [
                            'name' => '3번째 이미지 URL',
                            'key' => 'img2',
                            'help' => '3번째 이미지의 URL입니다.',
                            'default' => 'https://vcdn-vnexpress.vnecdn.net/2017/04/27/grape-4660-1493287310.jpg'
                        ]
                    )->for(ComponentType::first(), 'type'),
                'option'
            )->has(
                ComponentOption::factory()
                    ->state(
                        [
                            'name' => '4번째 링크 URL',
                            'key' => 'url3',
                            'help' => '4번째 이미지를 클릭했을 때 표시될 링크입니다.',
                            'default' => 'https://en.wikipedia.org/wiki/Peach'
                        ]
                    )->for(ComponentType::first(), 'type'),
                'option'
            )->has(
                ComponentOption::factory()
                    ->state(
                        [
                            'name' => '4번째 이미지 URL',
                            'key' => 'img3',
                            'help' => '4번째 이미지의 URL입니다.',
                            'default' => 'https://www.gardeningknowhow.com/wp-content/uploads/2021/07/peach-with-half-and-leaves-400x300.jpg'
                        ]
                    )->for(ComponentType::first(), 'type'),
                'option'
            ),
            'version'
        )->state([
            'name' => $componentName,
            'first_category' => 'design',
            'iconv' => 'image'
        ])->create();

        // 테마 상품 생성
        ThemeProduct::factory()->for(
            UserPartner::first(),
            'creator'
        )->has(
            ThemeProductInformation::factory(),
            'themeInformation'
        )->has(
        // 테마
            Theme::factory()->for(
                Solution::offset(1)->first(),
                'solution'
            )->has(
            // 에디터 지원 페이지
                EditablePage::factory()->state([
                    'name' => '메인'
                ])->for(
                    Solution::first()->supportedEditablePage()->first(),
                    'supportedEditablePage'
                )->has(
                // 에디터 지원 페이지 레이아웃
                    EditablePageLayout::factory()->for(
                        LinkedComponentGroup::factory(),
                        'linkedHeaderComponentGroup'
                    )->for(
                    // 연동 컴포넌트 그룹
                        LinkedComponentGroup::factory()->has(
                        // 연동 컴포넌트
                            LinkedComponent::factory()->count(3)->for(
                                $component,
                                'component'
                            )->state([
                                'name' => $componentName
                            ])->has(
                                // 연동 컴포넌트 옵션
                                LinkedComponentOption::factory()->for(
                                    $component->version->first()->option->skip(0)->first(),
                                    'componentOption'
                                ),
                                'linkedOption'
                            )->has(
                                LinkedComponentOption::factory()->for(
                                    $component->version->first()->option->skip(1)->first(),
                                    'componentOption'
                                ),
                                'linkedOption'
                            )->has(
                                LinkedComponentOption::factory()->for(
                                    $component->version->first()->option->skip(2)->first(),
                                    'componentOption'
                                ),
                                'linkedOption'
                            )->has(
                                LinkedComponentOption::factory()->for(
                                    $component->version->first()->option->skip(3)->first(),
                                    'componentOption'
                                ),
                                'linkedOption'
                            )->has(
                                LinkedComponentOption::factory()->for(
                                    $component->version->first()->option->skip(4)->first(),
                                    'componentOption'
                                ),
                                'linkedOption'
                            )->has(
                                LinkedComponentOption::factory()->for(
                                    $component->version->first()->option->skip(5)->first(),
                                    'componentOption'
                                ),
                                'linkedOption'
                            )->has(
                                LinkedComponentOption::factory()->for(
                                    $component->version->first()->option->skip(6)->first(),
                                    'componentOption'
                                ),
                                'linkedOption'
                            )->has(
                                LinkedComponentOption::factory()->for(
                                    $component->version->first()->option->skip(7)->first(),
                                    'componentOption'
                                ),
                                'linkedOption'
                            ),
                            'linkedComponent'
                        ),
                        'linkedContentComponentGroup'
                    )->for(
                        LinkedComponentGroup::factory(),
                        'linkedFooterComponentGroup'
                    ),
                    'editablePageLayout'
                )
            )
        )->create([
            'name' => '큐픽 테마 상품'
        ]);
    }
}
