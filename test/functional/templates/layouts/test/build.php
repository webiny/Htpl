<?php

$layoutTree =
[
    'blocks' => [
        'content-left' => 'inner content',
        'content-right' => 'inner content',
    ],
    'parent' =>[
        'blocks' => [
            'content-left' => 'parent inner content',
            'content-right' => 'parent inner content',
            'content' => 'parent content'
        ],
        'parent' => [
            'blocks' => [
                'content' => 'master parent content'
            ]
        ]
    ]
];

