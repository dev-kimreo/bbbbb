<?php

namespace Database\Factories\Themes;

use App\Models\Solution;
use App\Models\Themes\Theme;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;
use Str;

class ThemeFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Theme::class;
    public array $status = ['registering', 'registered'];

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        if (app()->environment() == 'production') {
            return [
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ];
        } else {
            return [
                'solution_id' => Solution::query()->inRandomOrder()->first()->getAttribute('id'),
                'status' => $this->status[array_rand($this->status)],
                'display' => rand(0, 1),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ];
        }

    }
}
