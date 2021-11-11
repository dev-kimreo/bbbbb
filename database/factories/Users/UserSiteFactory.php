<?php

namespace Database\Factories\Users;

use App\Models\Users\UserSite;
use Illuminate\Database\Eloquent\Factories\Factory;

class UserSiteFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = UserSite::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'type' => $this->faker->text(12),
            'name' => $this->faker->text(16),
            'url' => $this->faker->url,
            'solution' => $this->faker->text(16),
            'solution_user_id' => $this->faker->text(16),
            'apikey' => $this->faker->text(16),
        ];
    }
}