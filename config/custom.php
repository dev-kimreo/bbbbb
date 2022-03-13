<?php

return [

    'user' => [
        'toInactiveDays' => 365,
        'toAutoWithdrawalDays' => 365,
        'toDestructDays' => 90,
        'pricingType' => [
            'free' => 0,
            'standard' => 1
        ]
    ],

    'attach' => [
        'componentUploadImage' => [
            'fileUploadLimit' => 1048576,
            'totalStorageLimit' => [
                'free' => 104857600,
                'standard' => 4294967295
            ]
        ]
    ]
];
