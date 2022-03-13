<?php

namespace Database\Factories\UserThemes;

use App\Models\UserThemes\UserThemeSaveHistory;
use Illuminate\Database\Eloquent\Factories\Factory;

class UserThemeSaveHistoryFactory extends Factory
{
    protected $model = UserThemeSaveHistory::class;

    public function definition(): array
    {
        return [
            'data' => '{"tmp":"' . $this->faker->text(20) . '"}'
        ];
    }
}
