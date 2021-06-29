<?php

namespace Database\Factories\Exhibitions;

use App\Models\Exhibitions\Popup;
use Illuminate\Database\Eloquent\Factories\Factory;

class PopupFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Popup::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'title' => $this->faker->text(20),
        ];
    }
}
