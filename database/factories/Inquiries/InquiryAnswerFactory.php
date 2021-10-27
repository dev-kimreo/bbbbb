<?php

namespace Database\Factories\Inquiries;

use App\Models\Inquiries\InquiryAnswer;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

class InquiryAnswerFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = InquiryAnswer::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        return [
            'user_id' => 0,
            'inquiry_id' => 0,
            'answer' => $this->faker->text(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
    }
}
