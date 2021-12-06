<?php

namespace Database\Seeders;

use App\Models\Components\ComponentType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Testing\WithFaker;

class ComponentTypeSeeder extends Seeder
{
    use WithFaker;

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $insArrs = [
            [
                'name' => 'Toggle',
                'is_plural' => false,
                'has_option' => false,
                'has_default' => true,
                'max_count' => 1,
                'attributes' => null,
            ],
            [
                'name' => 'Text Field',
                'is_plural' => false,
                'has_option' => false,
                'has_default' => false,
                'max_count' => 1,
                'attributes' => [
                    "textMaxLength" => 99
                ],
            ],
            [
                'name' => 'Textarea',
                'is_plural' => false,
                'has_option' => false,
                'has_default' => false,
                'max_count' => 1,
                'attributes' => null,
            ],
            [
                'name' => 'Radio',
                'is_plural' => false,
                'has_option' => true,
                'has_default' => true,
                'max_count' => 1,
                'attributes' => null
            ],
            [
                'name' => 'Checkbox',
                'is_plural' => false,
                'has_option' => true,
                'has_default' => true,
                'max_count' => 1,
                'attributes' => null
            ],
            [
                'name' => 'Image Display',
                'is_plural' => true,
                'has_option' => false,
                'has_default' => false,
                'max_count' => 1,
                'attributes' => null
            ],
            [
                'name' => 'Image URL Display',
                'is_plural' => true,
                'has_option' => false,
                'has_default' => false,
                'max_count' => 1,
                'attributes' => null
            ],
            [
                'name' => 'Text + URL Display',
                'is_plural' => true,
                'has_option' => false,
                'has_default' => true,
                'max_count' => 1,
                'attributes' => [
                    "textMaxLength" => 25
                ]
            ]
        ];

        $propertiesArrs = [
            // Toggle
            [
                ['type' => 'boolean']
            ],
            // TextField
            [
                ['type' => 'text']
            ],
            // Textarea
            [
                ['type' => 'text']
            ],
            // Radio
            [
                ['type' => 'boolean']
            ],
            // Checkbox
            [
                ['type' => 'text']
            ],
            // Image Display
            [
                ['type' => 'file'],
                ['type' => 'alt'],
            ],
            // Image URL Display
            [
                ['type' => 'file'],
                ['type' => 'alt'],
                ['type' => 'url'],
            ],
            // Text + URL Display
            [
                ['type' => 'text'],
                ['type' => 'url'],
            ],
        ];

        foreach ($insArrs as $k => $v) {
            ComponentType::create($v)->properties()->createMany($propertiesArrs[$k]);
        }
    }
}
