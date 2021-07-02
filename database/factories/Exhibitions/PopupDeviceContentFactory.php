<?php

namespace Database\Factories\Exhibitions;

use App\Models\Exhibitions\PopupDeviceContent;
use Illuminate\Database\Eloquent\Factories\Factory;

class PopupDeviceContentFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = PopupDeviceContent::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'device' => array_rand(array_flip(PopupDeviceContent::$device)),
            'contents' => $this->faker->text(rand(50,400))
        ];
    }
}
