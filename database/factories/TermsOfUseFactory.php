<?php

namespace Database\Factories;

use App\Models\TermsOfUse;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;
use Str;

class TermsOfUseFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = TermsOfUse::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'user_id' => 0,
            'type' => collect(TermsOfUse::$types)->random(1)->pop(),
            'title' => $this->faker->realText(16),
            'start_at' => Carbon::now()->addWeeks(),
            'history' => $this->faker->realText(16),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
    }
}
