<?php

return [
    'ctrl' => [
        'title' =>  'Message',
        'label' => 'subject',
        'crdate' => 'crdate',
        'typeicon_classes' => [
            'default' => 'content-elements-mailform',
        ],
        'rootLevel' => 1,
        'hideTable' => true,
    ],
    'types' => [
        '0' => [
            'showitem' => 'message_id,subject,to,from,source',
        ],
    ],
    'palettes' => [],
    'columns' => [
        'message_id' => [
            'label' => 'Message ID',
            'config' => [
                'type' => 'input',
            ],
        ],
        'subject' => [
            'label' => 'Subject',
            'config' => [
                'type' => 'input',
            ],
        ],
        'to' => [
            'label' => 'To',
            'config' => [
                'type' => 'text',
            ],
        ],
        'from' => [
            'label' => 'From',
            'config' => [
                'type' => 'input',
            ],
        ],
        'source' => [
            'label' => 'Source',
            'config' => [
                'type' => 'text',
            ],
        ],
        'serialized' => [
            'label' => 'Serialized',
            'config' => [
                'type' => 'text',
            ],
        ],
        'crdate' => [
            'config' => [
                'type' => 'passthrough',
            ],
        ],
    ],
];
