<?php

namespace Database\Factories\Widgets;

use App\Models\Users\User;
use App\Models\Widgets\Widget;
use App\Models\Widgets\WidgetUsage;
use Illuminate\Database\Eloquent\Factories\Factory;

class WidgetUsageFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = WidgetUsage::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'sort' => rand(0, 255)
        ];
    }
}
