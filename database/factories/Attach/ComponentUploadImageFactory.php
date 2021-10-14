<?php

namespace Database\Factories\Attach;

use App\Models\Attach\ComponentUploadImage;
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
            'width' => $this->faker->numberBetween(128, 1280),
            'height' => $this->faker->numberBetween(128, 1280)
        ];
    }
}
