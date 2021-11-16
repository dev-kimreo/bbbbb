<?php

namespace Database\Factories\Themes;

use App\Models\Themes\ThemeProduct;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;
use Str;

class ThemeProductFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ThemeProduct::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        if (app()->environment() == 'production') {
            return [
                'name' => $this->faker->realText(16),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ];
        } else {
            return [
                'name' => $this->faker->realText(16),
                'display' => rand(0, 1),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ];
        }
    }
}
