<?php

namespace Database\Factories\Widgets;

use App\Models\Widgets\Widget;
use Illuminate\Database\Eloquent\Factories\Factory;

class WidgetFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Widget::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->text(rand(8, 16)),
            'description' => $this->faker->text(rand(32, 120)),
            'enable' => $this->faker->boolean(),
            'only_for_manager' => $this->faker->boolean()
        ];
    }
}
