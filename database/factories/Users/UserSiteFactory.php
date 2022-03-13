<?php

namespace Database\Factories\Users;

use App\Models\Users\UserSite;
use Illuminate\Database\Eloquent\Factories\Factory;

class UserSiteFactory extends Factory
{
    protected $model = UserSite::class;

    public function definition()
    {
        return [
            'name' => $this->faker->name(),
            'url' => $this->faker->url(),
            'biz_type' => $this->faker->text(16)
        ];
    }
}
