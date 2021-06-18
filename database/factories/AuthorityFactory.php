<?php

namespace Database\Factories;

use App\Models\Authority;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;
use Str;

class AuthorityFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Authority::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'code' => $this->faker->unique(true)->numberBetween(1, 10000),
            'title' => $this->faker->realText(16),
            'display_name' => $this->faker->realText(16),
            'memo' => $this->faker->realText(16),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
    }
}
