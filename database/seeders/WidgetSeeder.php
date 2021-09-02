<?php

namespace Database\Seeders;

use App\Models\Manager;
use App\Models\Widgets\Widget;
use App\Models\Widgets\WidgetUsage;
use Illuminate\Database\Seeder;

class WidgetSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $manager = Manager::limit(2)->get();

        $widgets = collect(
            array_merge(
                Widget::factory()->count(3)->create(['user_id' => $manager->get(0)->id])->toArray(),
                Widget::factory()->count(3)->create(['user_id' => $manager->get(1)->id])->toArray()
            )
        );

        $widgets->each(function ($m) use ($manager) {
            if (rand(1, 10) <= 7) {
                WidgetUsage::factory()->create(['widget_id' => $m['id'], 'user_id' => $manager->get(0)->id]);
            }
            if (rand(1, 10) <= 7) {
                WidgetUsage::factory()->create(['widget_id' => $m['id'], 'user_id' => $manager->get(1)->id]);
            }
        });

        $arr = [];
        WidgetUsage::get()->each(function ($v) use (&$arr) {
            $v->sort = $arr[$v->user_id] = ($arr[$v->user_id] ?? 0) + 1;
            $v->save();
        });
    }
}
