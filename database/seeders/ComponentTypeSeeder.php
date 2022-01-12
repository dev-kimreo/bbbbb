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
                'attributes' => [],
            ],
            [
                'name' => 'Text Field',
                'is_plural' => false,
                'has_option' => false,
                'has_default' => false,
                'max_count' => 1,
                'attributes' => ['textMaxLength'],
            ],
            [
                'name' => 'Textarea',
                'is_plural' => false,
                'has_option' => false,
                'has_default' => false,
                'max_count' => 1,
                'attributes' => [],
            ],
            [
                'name' => 'Radio',
                'is_plural' => false,
                'has_option' => true,
                'has_default' => true,
                'max_count' => 1,
                'attributes' => []
            ],
            [
                'name' => 'Checkbox',
                'is_plural' => false,
                'has_option' => true,
                'has_default' => true,
                'max_count' => 1,
                'attributes' => []
            ],
            [
                'name' => 'Image Display',
                'is_plural' => true,
                'has_option' => false,
                'has_default' => false,
                'max_count' => 1,
                'attributes' => []
            ],
            [
                'name' => 'Image URL Display',
                'is_plural' => true,
                'has_option' => false,
                'has_default' => false,
                'max_count' => 1,
                'attributes' => []
            ],
            [
                'name' => 'Select',
                'is_plural' => false,
                'has_option' => true,
                'has_default' => true,
                'max_count' => 1,
                'attributes' => []
            ],
            [
                'name' => 'Number',
                'is_plural' => false,
                'has_option' => false,
                'has_default' => true,
                'max_count' => 1,
                'attributes' => ['unit'],
            ],
            [
                'name' => 'Color Picker',
                'is_plural' => false,
                'has_option' => false,
                'has_default' => true,
                'max_count' => 1,
                'attributes' => [],
            ],
            [
                'name' => 'Icon Picker',
                'is_plural' => false,
                'has_option' => false,
                'has_default' => true,
                'max_count' => 1,
                'attributes' => [],
            ],
            [
                'name' => 'Text + URL Display',
                'is_plural' => true,
                'has_option' => false,
                'has_default' => true,
                'max_count' => 1,
                'attributes' => ['textMaxLength']
            ],
        ];

        $propertiesArrs = [
            // Toggle
            [
                [
                    'type' => 'boolean',
                    'preset' => 'toggle'
                ]
            ],
            // TextField
            [
                [
                    'type' => 'text',
                    'preset' => 'text'
                ]
            ],
            // Textarea
            [
                [
                    'type' => 'text',
                    'preset' => 'textarea'
                ]
            ],
            // Radio
            [
                [
                    'type' => 'text',
                    'preset' => 'radio'
                ]
            ],
            // Checkbox
            [
                [
                    'type' => 'text',
                    'preset' => 'checkbox'
                ]
            ],
            // Image Display
            [
                [
                    'type' => 'file',
                    'preset' => 'image'
                ],
                [
                    'type' => 'alt',
                    'preset' => 'alt'
                ],
            ],
            // Image URL Display
            [
                [
                    'type' => 'file',
                    'preset' => 'image'
                ],
                [
                    'type' => 'alt',
                    'preset' => 'alt'
                ],
                [
                    'type' => 'url',
                    'preset' => 'url'
                ],
                [
                    'type' => 'text',
                    'preset' => 'target'
                ]
            ],
            // Select
            [
                [
                    'type' => 'text',
                    'preset' => 'select'
                ]
            ],
            // Number
            [
                [
                    'type' => 'integer',
                    'preset' => 'number'
                ]
            ],
            // Color Picker
            [
                [
                    'type' => 'color',
                    'preset' => 'color'
                ],
                [
                    'type' => 'integer',
                    'preset' => 'alpha'
                ]
            ],
            // Icon Picker
            [
                [
                    'type' => 'icon',
                    'preset' => 'text'
                ],
                [
                    'type' => 'color',
                    'preset' => 'color'
                ]
            ],
            // Text + URL Display
            [
                [
                    'type' => 'text',
                    'preset' => 'text'
                ],
                [
                    'type' => 'url',
                    'preset' => 'url'
                ],
                [
                    'type' => 'text',
                    'preset' => 'target'
                ]
            ],
        ];

        foreach ($insArrs as $k => $v) {
            ComponentType::create($v)->properties()->createMany($propertiesArrs[$k]);
        }
    }
}
