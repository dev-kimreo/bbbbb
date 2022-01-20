<?php

namespace Database\Factories\Users;

use App\Models\Users\UserPartner;
use Illuminate\Database\Eloquent\Factories\Factory;

class UserPartnerFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = UserPartner::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->name
        ];
    }
}
