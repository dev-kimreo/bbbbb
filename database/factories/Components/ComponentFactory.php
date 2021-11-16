<?php

namespace Database\Factories\Components;

use App\Models\Components\Component;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;
use Str;

class ComponentFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Component::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'use_all_page' => 1,
            'display' => 1,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
    }
}
