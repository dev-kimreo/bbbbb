<?php

namespace Database\Factories\Inquiries;

use App\Models\Inquiries\Inquiry;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

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
    public function definition(): array
    {
        return [
            'user_id' => 0,
            'title' => $this->faker->text(30),
            'question' => $this->faker->text(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
    }
}
