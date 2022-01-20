<?php

namespace Database\Factories\Components;

use App\Models\Components\Component;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;
use Str;

class ComponentFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Component::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->text(16),
            'use_other_than_maker' => rand(0, 1),
            'first_category' => $this->faker->text(12),
            'use_blank' => rand(0, 1),
            'use_all_page' => 1,
            'icon' => $this->faker->text(16),
            'display' => 1,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
    }
}
