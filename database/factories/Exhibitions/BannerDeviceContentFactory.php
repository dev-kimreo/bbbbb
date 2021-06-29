<?php

namespace Database\Factories\Exhibitions;

use App\Models\Exhibitions\BannerDeviceContent;
use Illuminate\Database\Eloquent\Factories\Factory;

class BannerDeviceContentFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = BannerDeviceContent::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        return [
            'device' => array_rand(array_flip(BannerDeviceContent::$device)),
        ];
    }
}
