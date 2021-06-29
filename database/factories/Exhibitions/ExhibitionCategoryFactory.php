<?php

namespace Database\Factories\Exhibitions;

use App\Models\Exhibitions\ExhibitionCategory;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

class ExhibitionCategoryFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ExhibitionCategory::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->text(8),
            'url' => $this->faker->url,
            'division' => array_rand(array_flip(ExhibitionCategory::$divisions)),
            'site' => array_rand(array_flip(ExhibitionCategory::$sites)),
            'max' => rand(1,100),
            'enable' => rand(0, 1),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
    }
}
