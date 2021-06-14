<?php

namespace Database\Factories;

use App\Models\Board;
use Illuminate\Database\Eloquent\Factories\Factory;

class BoardFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Board::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => '공지사항',
            'enable' => 0,
            'options' => [
                'board' => 'all',
                'theme' => 'boardDefaultTheme',
                'thumbnail' => 1,
                'reply' => 1,
                'editor' => 'all',
                'attach' => 1,
                'attachLimit' => 10,
                'createdAt' => 1
            ]
        ];
    }
}
