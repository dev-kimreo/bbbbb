<?php

namespace Database\Factories;

use App\Models\AttachFile;
use Illuminate\Database\Eloquent\Factories\Factory;

class AttachFileFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = AttachFile::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'server' => 'public',
            'url' => $this->faker->url,
            'path' => 'banner_content/'
                . substr('000' . rand(0, 99), -3) . '/'
                . substr('000' . rand(0, 99), -3),
            'name' => $this->faker->text(32) . '.png',
            'org_name' => $this->faker->text(rand(8, 16)) . '.png',
            'etc' => [],
        ];
    }
}
