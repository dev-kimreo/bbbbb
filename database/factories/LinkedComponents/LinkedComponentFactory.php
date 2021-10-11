<?php

namespace Database\Factories\LinkedComponents;

use App\Models\LinkedComponents\LinkedComponent;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;
use Str;

class LinkedComponentFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = LinkedComponent::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'sort' => 1,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
    }
}
