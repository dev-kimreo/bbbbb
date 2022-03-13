<?php

namespace Database\Seeders;

use App\Models\Attach\AttachFile;
use App\Models\Components\Component;
use App\Models\Components\ComponentOption;
use App\Models\Components\ComponentOptionProperty;
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
                            'file_name' => 'index'
                        ]
                    ]
                );
            });
        }

//        // 컴포넌트 텍스트 옵션 유형 속성 추가
//        ComponentTypeProperty::firstOrCreate(
//            ['component_type_id' => $componentType->id],
//            [
//                'type' => 'text',
//            ]
//        );

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
            AttachFile::factory()->for(UserPartner::first(), 'uploader')
        )->has(
            ComponentVersion::factory()->state([
                'usable' => true,
                'template' => $html,
                'style' => $css,
                'script' => $script
            ])->has(
                ComponentOption::factory()->state([
                    'name' => '빅 배너',
                    'key' => 'BigBanner',
                    'help' => '빅 배너 입니다.',
//                            'default' => 'https://en.wikipedia.org/wiki/Strawberry'
                ])->for(
                    ComponentType::where('name', 'Image URL Display')->first()
                    , 'type'
                )->has(
                    ComponentOptionProperty::factory()->for(
                        ComponentType::where('name', 'Image URL Display')->first()->properties->skip(0)->first(),
                        'property'
                    )->state([
                        'key' => 'image',
                        'name' => '이미지',
                        'initial_value' => 'https://블라블라~'
                    ]),
                    'properties'
                )->has(
                    ComponentOptionProperty::factory()->for(
                        ComponentType::where('name', 'Image URL Display')->first()->properties->skip(1)->first(),
                        'property'
                    )->state([
                        'key' => 'alt',
                        'name' => '이미지 alt',
                        'initial_value' => 'https://블라블라~'
                    ]),
                    'properties'
                )->has(
                    ComponentOptionProperty::factory()->for(
                        ComponentType::where('name', 'Image URL Display')->first()->properties->skip(2)->first(),
                        'property'
                    )->state([
                        'key' => 'url',
                        'name' => '연결 url',
                        'initial_value' => 'https://블라블라로이동'
                    ]),
                    'properties'
                ),
                'options'
            ),
            'version'
        )->state([
            'name' => $componentName,
            'first_category' => 'design',
            'icon' => 'image'
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
                            LinkedComponent::factory()->count(1)->for(
                                $component,
                                'component'
                            )->state([
                                'name' => $componentName
                            ])->has(
                                // 연동 컴포넌트 옵션
                                LinkedComponentOption::factory()->for(
                                    $component->version->first()->options->skip(0)->first(),
                                    'componentOption'
                                )->state([
                                    'value' => [
                                        'image' => 'https://블라블라~',
                                        'alt'   => '이미지거든',
                                        'url'   => 'https://샬라샬라~'
                                    ]
                                ]),
                                'linkedOptions'
                            ),
                            'linkedComponents'
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
