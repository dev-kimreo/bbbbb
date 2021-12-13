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
                [
                    'type' => 'boolean',
                    'prefix' => 'toggle'
                ]
            ],
            // TextField
            [
                [
                    'type' => 'text',
                    'prefix' => 'text'
                ]
            ],
            // Textarea
            [
                [
                    'type' => 'text',
                    'prefix' => 'textarea'
                ]
            ],
            // Radio
            [
                [
                    'type' => 'text',
                    'prefix' => 'radio'
                ]
            ],
            // Checkbox
            [
                [
                    'type' => 'text',
                    'prefix' => 'checkbox'
                ]
            ],
            // Image Display
            [
                [
                    'type' => 'file',
                    'prefix' => 'image'
                ],
                [
                    'type' => 'alt',
                    'prefix' => 'alt'
                ],
            ],
            // Image URL Display
            [
                [
                    'type' => 'file',
                    'prefix' => 'image'
                ],
                [
                    'type' => 'alt',
                    'prefix' => 'alt'
                ],
                [
                    'type' => 'url',
                    'prefix' => 'url'
                ],
            ],
            // Text + URL Display
            [
                [
                    'type' => 'text',
                    'prefix' => 'text'
                ],
                [
                    'type' => 'url',
                    'prefix' => 'url'
                ],
            ],
        ];

        foreach ($insArrs as $k => $v) {
            ComponentType::create($v)->properties()->createMany($propertiesArrs[$k]);
        }
    }
}
