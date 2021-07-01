<?php

namespace Database\Factories\Users;

use App\Models\Users\UserPrivacyActive;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class UserPrivacyActiveFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = UserPrivacyActive::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->name,
            'email' => $this->faker->unique()->safeEmail,
        ];
    }

}
