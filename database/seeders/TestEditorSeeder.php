<?php

namespace Database\Seeders;

use App\Models\Components\Component;
use App\Models\Components\ComponentOption;
use App\Models\Components\ComponentType;
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
use App\Models\Themes\ThemeGood;
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
            Solution::first()->supportedEditablePage()->create([
                'name' => '메인',
                'file_name' => 'main.html'
            ]);
        }

        // 컴포넌트 텍스트 유형 추가
        ComponentType::firstOrCreate(
            ['type' => 'text'],
            [
                'name' => '텍스트형',
                'html' => '<div><input type="text" name="::key::" value="::value::" /></div>',
                'style' => '',
                'script' => '',
            ]
        );

        // 컴포넌트
        $component = Component::factory()->for(
            UserPartner::first(),
            'creator'
        )->for(
            Solution::first(),
            'solution'
        )->has(
            ComponentVersion::factory()->state([
                'template' => '<div><h2><!--data.title--></h2></div>',
                'style' => '',
                'script' => '<script>alert(data.title);</script>',
            ])->has(
                ComponentOption::factory()->state([
                    'name' => '배너 타이틀',
                    'key' => 'title',
                    'default' => '배너 타이틀 입니다.'
                ])->for(
                    ComponentType::first(),
                    'type'
                ),
                'option'
            ),
            'version'
        )->state([
            'name' => '메인 배너'
        ])->create();

        // 테마 상품 생성
        ThemeGood::factory()->for(
            UserPartner::first(),
            'creator'
        )->has(
            // 테마
            Theme::factory()->for(
                Solution::first(),
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
                            )->has(
                                // 연동 컴포넌트 옵션
                                LinkedComponentOption::factory()->for(
                                    $component->version->first()->option->first(),
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
