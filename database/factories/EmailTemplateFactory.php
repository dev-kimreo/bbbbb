<?php

namespace Database\Factories;

use App\Models\EmailTemplate;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;
use Str;

class EmailTemplateFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = EmailTemplate::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'user_id' => 0,
            'code' => $this->faker->realText(16),
            'name' => $this->faker->realText(16),
            'enable' => 1,
            'ignore_agree' => 1,
            'title' => $this->faker->realText(16),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
    }
}
