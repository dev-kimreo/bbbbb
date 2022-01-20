<?php

namespace Database\Factories\Components;

use App\Models\Components\ComponentOption;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;
use Str;

class ComponentOptionFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ComponentOption::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->text(12),
            'key' => $this->faker->text(10),
            'hideable' => rand(0, 1),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
    }
}
