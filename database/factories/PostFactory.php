<?php

namespace Database\Factories;

use App\Models\Post;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;
use Str;

class PostFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Post::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'board_id' => 0,
            'user_id' => 0,
            'title' => $this->faker->realText(30),
            'content' => $this->faker->realText(),
            'hidden' => 0,
            'sort' => 999,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
    }
}
