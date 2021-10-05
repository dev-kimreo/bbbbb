<?php

namespace Database\Factories;

use App\Models\ComponentUploadImage;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

class ComponentUploadImageFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ComponentUploadImage::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        return [
            'url_thumb' => $this->faker->url,
            'width' => $this->faker->numberBetween(128, 1280),
            'height' => $this->faker->numberBetween(128, 1280),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
    }
}
