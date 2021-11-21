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
use Illuminate\Support\Str;

class activeThemeSeeder extends Seeder
{
    use WithFaker;

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        ini_set('memory_limit','-1');
//        $this->setUpFaker();
        // 테마 상품 생성
        ThemeProduct::factory()->for(
            UserPartner::query()->inRandomOrder()->first(),
            'creator'
        )->has(
            ThemeProductInformation::factory(),
            'themeInformation'
        )->has(
        // 테마
            Theme::factory()->count(2)
        )->count(100000)->create();
    }
}
