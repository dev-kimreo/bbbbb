<?php

namespace Database\Factories;

use App\Models\Inquiry;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;
use Str;

class InquiryFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Inquiry::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'user_id' => 0,
            'title' => $this->faker->text(30),
            'question' => $this->faker->text(200),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
    }
}
