<?php

return [
    'credits' => [
        'global_budget' => (int) env('SECTORS_CREDIT_GLOBAL_BUDGET', 1000),
        'daily_user_quota' => (int) env('SECTORS_CREDIT_DAILY_USER_QUOTA', 20),
        'max_attempts' => (int) env('SECTORS_CREDIT_MAX_ATTEMPTS', 2),
        'timezone' => env('SECTORS_CREDIT_TIMEZONE', 'Asia/Jakarta'),
    ],
];
