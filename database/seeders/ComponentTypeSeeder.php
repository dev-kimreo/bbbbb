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
                'isPlural' => false,
                'hasOption' => false,
                'hasDefault' => true,
                'maxCount' => 1,
                'attributes' => null,
            ],
            [
                'name' => 'Text Field',
                'isPlural' => false,
                'hasOption' => false,
                'hasDefault' => false,
                'maxCount' => 1,
                'attributes' => [
                    "textMaxLength" => 99
                ],
            ],
            [
                'name' => 'Textarea',
                'isPlural' => false,
                'hasOption' => false,
                'hasDefault' => false,
                'maxCount' => 1,
                'attributes' => null,
            ],
            [
                'name' => 'Radio',
                'isPlural' => false,
                'hasOption' => true,
                'hasDefault' => true,
                'maxCount' => 1,
                'attributes' => null
            ],
            [
                'name' => 'Checkbox',
                'isPlural' => false,
                'hasOption' => true,
                'hasDefault' => true,
                'maxCount' => 1,
                'attributes' => null
            ],
            [
                'name' => 'Image Display',
                'isPlural' => true,
                'hasOption' => false,
                'hasDefault' => false,
                'maxCount' => 1,
                'attributes' => null
            ],
            [
                'name' => 'Image URL Display',
                'isPlural' => true,
                'hasOption' => false,
                'hasDefault' => false,
                'maxCount' => 1,
                'attributes' => null
            ],
            [
                'name' => 'Text + URL Display',
                'isPlural' => true,
                'hasOption' => false,
                'hasDefault' => true,
                'maxCount' => 1,
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
