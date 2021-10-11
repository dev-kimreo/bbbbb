<?php

namespace Database\Factories\EditablePages;

use App\Models\EditablePages\EditablePageLayout;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;
use Str;

class EditablePageLayoutFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = EditablePageLayout::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
    }
}
