<?php

namespace Database\Factories;

use App\Models\TranslationContent;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;
use Str;

class TranslationContentFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = TranslationContent::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'lang' => 'ko',
            'value' => $this->faker->realText(16),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
    }
}
