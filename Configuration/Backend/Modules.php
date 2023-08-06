<?php

use R3H6\MailCatcher\Controller\MessageController;

return [
    'mailcatcher' => [
        'parent' => 'system',
        'position' => ['top'],
        'access' => 'user',
        'workspaces' => 'live',
        'path' => '/module/mailcatcher/message',
        'labels' => 'LLL:EXT:mail_catcher/Resources/Private/Language/locallang_mod.xlf',
        'extensionName' => 'MailCatcher',
        'controllerActions' => [
            MessageController::class => [
                'index', 'show', 'preview', 'download', 'deleteAll', 'forward',
            ],
        ],
    ],
];
