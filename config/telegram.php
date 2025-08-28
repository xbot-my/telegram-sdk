<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Default Bot Instance
    |--------------------------------------------------------------------------
    |
    | 指定默认使用的 Bot 实例名称。当不指定 Bot 名称时将使用此实例。
    |
    */
    'default' => env('TELEGRAM_DEFAULT_BOT', 'main'),

    /*
    |--------------------------------------------------------------------------
    | Bot Instances Configuration
    |--------------------------------------------------------------------------
    |
    | 配置多个 Bot 实例，每个实例都有独立的 Token 和配置。
    | 实例间完全隔离，互不干扰。
    |
    */
    'bots' => [
        'main' => [
            'token' => env('TELEGRAM_MAIN_BOT_TOKEN'),
            'base_url' => env('TELEGRAM_BASE_URL', 'https://api.telegram.org/bot'),
            'timeout' => (int) env('TELEGRAM_TIMEOUT', 30),
            'retry_attempts' => (int) env('TELEGRAM_RETRY_ATTEMPTS', 3),
            'retry_delay' => (int) env('TELEGRAM_RETRY_DELAY', 1000), // milliseconds
            'webhook_url' => env('TELEGRAM_MAIN_WEBHOOK_URL'),
            'webhook_secret' => env('TELEGRAM_MAIN_WEBHOOK_SECRET'),
            'middleware' => ['auth', 'rate_limit'],
            'rate_limit' => [
                'enabled' => true,
                'max_requests' => 30,
                'per_seconds' => 60,
            ],
            'logging' => [
                'enabled' => env('TELEGRAM_LOGGING_ENABLED', true),
                'level' => env('TELEGRAM_LOG_LEVEL', 'info'),
                'channel' => env('TELEGRAM_LOG_CHANNEL', 'telegram'),
            ],
        ],

        'customer-service' => [
            'token' => env('TELEGRAM_CS_BOT_TOKEN'),
            'base_url' => env('TELEGRAM_BASE_URL', 'https://api.telegram.org/bot'),
            'timeout' => (int) env('TELEGRAM_CS_TIMEOUT', 15),
            'retry_attempts' => (int) env('TELEGRAM_CS_RETRY_ATTEMPTS', 2),
            'retry_delay' => (int) env('TELEGRAM_CS_RETRY_DELAY', 500),
            'webhook_url' => env('TELEGRAM_CS_WEBHOOK_URL'),
            'webhook_secret' => env('TELEGRAM_CS_WEBHOOK_SECRET'),
            'middleware' => ['auth'],
            'rate_limit' => [
                'enabled' => true,
                'max_requests' => 10,
                'per_seconds' => 60,
            ],
            'logging' => [
                'enabled' => env('TELEGRAM_CS_LOGGING_ENABLED', true),
                'level' => env('TELEGRAM_CS_LOG_LEVEL', 'info'),
                'channel' => env('TELEGRAM_CS_LOG_CHANNEL', 'telegram-cs'),
            ],
        ],

        'marketing' => [
            'token' => env('TELEGRAM_MARKETING_BOT_TOKEN'),
            'base_url' => env('TELEGRAM_BASE_URL', 'https://api.telegram.org/bot'),
            'timeout' => (int) env('TELEGRAM_MARKETING_TIMEOUT', 60),
            'retry_attempts' => (int) env('TELEGRAM_MARKETING_RETRY_ATTEMPTS', 5),
            'retry_delay' => (int) env('TELEGRAM_MARKETING_RETRY_DELAY', 2000),
            'webhook_url' => env('TELEGRAM_MARKETING_WEBHOOK_URL'),
            'webhook_secret' => env('TELEGRAM_MARKETING_WEBHOOK_SECRET'),
            'middleware' => ['rate_limit'],
            'rate_limit' => [
                'enabled' => true,
                'max_requests' => 100,
                'per_seconds' => 60,
            ],
            'logging' => [
                'enabled' => env('TELEGRAM_MARKETING_LOGGING_ENABLED', true),
                'level' => env('TELEGRAM_MARKETING_LOG_LEVEL', 'info'),
                'channel' => env('TELEGRAM_MARKETING_LOG_CHANNEL', 'telegram-marketing'),
            ],
        ],

        'admin' => [
            'token' => env('TELEGRAM_ADMIN_BOT_TOKEN'),
            'base_url' => env('TELEGRAM_BASE_URL', 'https://api.telegram.org/bot'),
            'timeout' => (int) env('TELEGRAM_ADMIN_TIMEOUT', 45),
            'retry_attempts' => (int) env('TELEGRAM_ADMIN_RETRY_ATTEMPTS', 3),
            'retry_delay' => (int) env('TELEGRAM_ADMIN_RETRY_DELAY', 1500),
            'webhook_url' => env('TELEGRAM_ADMIN_WEBHOOK_URL'),
            'webhook_secret' => env('TELEGRAM_ADMIN_WEBHOOK_SECRET'),
            'middleware' => ['auth', 'admin', 'ip_whitelist'],
            'rate_limit' => [
                'enabled' => true,
                'max_requests' => 50,
                'per_seconds' => 60,
            ],
            'logging' => [
                'enabled' => env('TELEGRAM_ADMIN_LOGGING_ENABLED', true),
                'level' => env('TELEGRAM_ADMIN_LOG_LEVEL', 'debug'),
                'channel' => env('TELEGRAM_ADMIN_LOG_CHANNEL', 'telegram-admin'),
            ],
            'security' => [
                'ip_whitelist' => array_filter(explode(',', env('TELEGRAM_ADMIN_IP_WHITELIST', ''))),
                'require_https' => env('TELEGRAM_ADMIN_REQUIRE_HTTPS', true),
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Global Settings
    |--------------------------------------------------------------------------
    |
    | 全局设置，应用于所有 Bot 实例
    |
    */
    'global' => [
        'user_agent' => env('TELEGRAM_USER_AGENT', 'XBot-Telegram-SDK/1.0'),
        'verify_ssl' => env('TELEGRAM_VERIFY_SSL', true),
        'proxy' => env('TELEGRAM_PROXY'),
        'cache' => [
            'enabled' => env('TELEGRAM_CACHE_ENABLED', true),
            'ttl' => (int) env('TELEGRAM_CACHE_TTL', 3600), // seconds
            'prefix' => env('TELEGRAM_CACHE_PREFIX', 'telegram'),
        ],
        'debug' => env('TELEGRAM_DEBUG', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Webhook Configuration
    |--------------------------------------------------------------------------
    |
    | 多实例 Webhook 路由配置
    |
    */
    'webhook' => [
        'route_prefix' => env('TELEGRAM_WEBHOOK_ROUTE_PREFIX', 'telegram/webhook'),
        'middleware' => ['api', 'telegram.webhook'],
        'verify_signature' => env('TELEGRAM_WEBHOOK_VERIFY_SIGNATURE', true),
        'max_connections' => (int) env('TELEGRAM_WEBHOOK_MAX_CONNECTIONS', 100),
        'allowed_updates' => [
            'message',
            'edited_message',
            'channel_post',
            'edited_channel_post',
            'inline_query',
            'chosen_inline_result',
            'callback_query',
            'shipping_query',
            'pre_checkout_query',
            'poll',
            'poll_answer',
            'my_chat_member',
            'chat_member',
            'chat_join_request',
        ],
    ],
];