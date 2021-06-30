<?php

namespace Database\Factories\Exhibitions;

use App\Models\Exhibitions\Banner;
use Illuminate\Database\Eloquent\Factories\Factory;

class BannerFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Banner::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        return [
            'title' => $this->faker->text(20),
            'url' => $this->faker->url,
            'ga_code' => $this->faker->text(64),
            'memo' => $this->faker->text(rand(50, 200)),
        ];
    }
}
