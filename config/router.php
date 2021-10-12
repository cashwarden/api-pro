<?php

return [
    'OPTIONS <module>/<controller:.+?>' => '<module>/user/options',

    "POST <module>/<alias:login|join>" => '<module>/user/<alias>',
    "POST <module>/token/refresh" => '<module>/user/refresh-token',
    "POST <module>/rules/<id:\d+>/copy" => '<module>/rule/copy',
    "PUT <module>/rules/<id:\d+>/status" => '<module>/rule/update-status',
    "GET <module>/accounts/types" => '<module>/account/types',
    "GET <module>/accounts/<id:\d+>/balances/trend" => '<module>/account/balances-trend',
    "GET <module>/accounts/overview" => '<module>/account/overview',
    "POST <module>/reset-token" => '<module>/user/reset-token',

    "GET <module>/users/auth-clients" => '<module>/user/get-auth-clients',
    "DELETE <module>/users/auth-client/<type:\w+>" => '<module>/user/delete-auth-client',
    'POST <module>/users/confirm' => '<module>/user/confirm',
    'POST <module>/users/send-confirmation' => '<module>/user/send-confirmation',
    'POST <module>/users/me' => '<module>/user/me-update',
    'GET <module>/users/me' => '<module>/user/me',
    'POST <module>/users/password-reset' => '<module>/user/password-reset',
    'POST <module>/users/change-password' => '<module>/user/change-password',
    'POST <module>/users/password-reset-token-verification' =>
        '<module>/user/password-reset-token-verification',
    'POST <module>/users/password-reset-request' => '<module>/user/password-reset-request',
    'POST <module>/users/upgrade-to-pro-request' => '<module>/user/upgrade-to-pro-request',
    "GET <module>/users/pro-record/<out_sn:\w+>" => '<module>/user/get-user-pro-record',
    "GET <module>/users/pro" => '<module>/user/get-user-pro',
    'GET <module>/users/settings' => '<module>/user/get-settings',
    'POST <module>/users/settings' => '<module>/user/update-settings',

    "GET <module>/transactions/<alias:types|export>" => '<module>/transaction/<alias>',
    "POST <module>/transactions/upload" => '<module>/transaction/upload',
    "POST <module>/transactions/by-description" => '<module>/transaction/create-by-description',

    "GET <module>/categories/analysis" => '<module>/category/analysis',

    "GET <module>/records/<alias:sources|overview|analysis|reimbursement-statuses>" =>
        '<module>/record/<alias>',
    "PUT <module>/records/status/<id:\d+>/<key:\w+>" => '<module>/record/update-status',

    "PUT <module>/recurrences/<id:\d+>/status" => '<module>/recurrence/update-status',
    "GET <module>/recurrences/frequencies" => '<module>/recurrence/frequency-types',

    "GET <module>/site-config" => '/site/data',
    "POST pay-notify-url" => '/site/pay-notify-url',
    "POST pay-return-url" => '/site/pay-return-url',
    "GET <module>/<alias:icons>" => '/site/<alias>',
    "GET health-check" => 'site/health-check',

    "GET <module>/ledgers/types" => '<module>/ledger/types',
    "GET <module>/ledgers/categories" => '<module>/ledger/categories',
    "GET <module>/ledgers/token/<token:\w+>" => '<module>/ledger/view-by-token',
    "POST <module>/ledgers/join/<token:\w+>" => '<module>/ledger/join-by-token',
    "POST <module>/ledger/members" => '<module>/ledger/inviting-member',
    "GET <module>/ledger/members" => '<module>/ledger-member/index',
    "PUT <module>/ledger/members/<id:\d+>" => '<module>/ledger-member/update',

    "POST <module>/budget-configs/<id:\d+>/copy" => '<module>/budget-config/copy',

    "PUT <module>/wish-lists/<id:\d+>/status" => '<module>/wish-list/update-status',

    "GET <module>/investments/overview" => '<module>/investment/overview',

    "GET <module>/currencies/rate/<from:\w+>/<to:\w+>" => '<module>/currency/rate',
    "GET <module>/currencies/codes" => '<module>/currency/codes',
    "GET <module>/currencies/can-use-codes" => '<module>/currency/can-use-codes',

    [
        'class' => 'yii\rest\UrlRule',
        'controller' => [
            'v1/account',
            'v1/category',
            'v1/rule',
            'v1/tag',
            'v1/record',
            'v1/transaction',
            'v1/recurrence',
            'v1/budget-config',
            'v1/ledger',
            'v1/currency',
            'v1/wish-list',
        ]
    ],
    // '<module>/<controller:\w+>/<action:\w+>/<id:\d+>' => '<module>/<controller>/<action>',
];
