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
    | HTTP Client Templates
    |--------------------------------------------------------------------------
    |
    | 预设的 HTTP 客户端配置模板，简化 Bot 实例创建。
    | 可根据不同场景选择合适的模板，支持覆盖特定配置。
    |
    */
    'http_client_templates' => [
        'standard' => [
            'timeout' => 30,
            'connect_timeout' => 10,
            'read_timeout' => 30,
            'retry_attempts' => 3,
            'retry_delay' => 1000,
            'verify_ssl' => true,
            'user_agent' => 'XBot-Telegram-SDK/1.0',
            'max_redirects' => 5,
            'debug' => false,
            'base_url' => 'https://api.telegram.org/bot',
        ],
        'fast' => [
            'timeout' => 15,
            'connect_timeout' => 5,
            'read_timeout' => 15,
            'retry_attempts' => 2,
            'retry_delay' => 500,
            'verify_ssl' => true,
            'user_agent' => 'XBot-Telegram-SDK/1.0',
            'max_redirects' => 3,
            'debug' => false,
            'base_url' => 'https://api.telegram.org/bot',
        ],
        'reliable' => [
            'timeout' => 60,
            'connect_timeout' => 15,
            'read_timeout' => 60,
            'retry_attempts' => 5,
            'retry_delay' => 2000,
            'verify_ssl' => true,
            'user_agent' => 'XBot-Telegram-SDK/1.0',
            'max_redirects' => 5,
            'debug' => false,
            'base_url' => 'https://api.telegram.org/bot',
        ],
        'debug' => [
            'timeout' => 30,
            'connect_timeout' => 10,
            'read_timeout' => 30,
            'retry_attempts' => 1,
            'retry_delay' => 0,
            'verify_ssl' => false,
            'user_agent' => 'XBot-Telegram-SDK/1.0 (Debug)',
            'max_redirects' => 5,
            'debug' => true,
            'base_url' => env('TELEGRAM_DEBUG_BASE_URL', 'https://api.telegram.org/bot'),
        ],
        'production' => [
            'timeout' => 45,
            'connect_timeout' => 12,
            'read_timeout' => 45,
            'retry_attempts' => 4,
            'retry_delay' => 1500,
            'verify_ssl' => true,
            'user_agent' => 'XBot-Telegram-SDK/1.0',
            'max_redirects' => 3,
            'debug' => false,
            'base_url' => 'https://api.telegram.org/bot',
            'headers' => [
                'X-Powered-By' => 'XBot-Telegram-SDK',
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Bot Token Validation
    |--------------------------------------------------------------------------
    |
    | 控制 Bot Token 的严格校验。可通过 pattern 覆盖默认正则，或设置 enabled
    | 为 false 关闭验证。
    |
    */
    'token_validation' => [
        'enabled' => env('TELEGRAM_VALIDATE_TOKEN', true),
        'pattern' => env('TELEGRAM_TOKEN_PATTERN', '^\d+:[A-Za-z0-9_-]{32,}$'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Bot Instances Configuration
    |--------------------------------------------------------------------------
    |
    | 配置多个 Bot 实例，每个实例都有独立的 Token 和配置。
    | 实例间完全隔离，互不干扰。
    |
    | 新增功能：
    | - http_template: 指定使用的 HTTP 客户端模板
    | - http_overrides: 覆盖模板中的特定配置
    |
    */
    'bots' => [
        'main' => [
            'token' => env('TELEGRAM_MAIN_BOT_TOKEN'),
            // 'token_validation' => [ // 可覆盖或关闭严格校验
            //     'enabled' => env('TELEGRAM_MAIN_VALIDATE_TOKEN', true),
            //     'pattern' => env('TELEGRAM_MAIN_TOKEN_PATTERN', null),
            // ],
            'http_template' => env('TELEGRAM_MAIN_HTTP_TEMPLATE', 'standard'),
            'http_overrides' => [
                'timeout' => (int) env('TELEGRAM_TIMEOUT', null), // 覆盖模板配置
                'base_url' => env('TELEGRAM_BASE_URL', null),
            ],
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

        // 客服机器人 - 使用快速响应模板
        'customer-service' => [
            'token' => env('TELEGRAM_CS_BOT_TOKEN'),
            'http_template' => 'fast',
            'http_overrides' => [
                // 可根据需要覆盖模板配置
            ],
            'webhook_url' => env('TELEGRAM_CS_WEBHOOK_URL'),
            'webhook_secret' => env('TELEGRAM_CS_WEBHOOK_SECRET'),
            'middleware' => ['auth', 'rate_limit'],
            'rate_limit' => [
                'enabled' => true,
                'max_requests' => 60,
                'per_seconds' => 60,
            ],
        ],

        // 营销机器人 - 使用高可靠性模板
        'marketing' => [
            'token' => env('TELEGRAM_MARKETING_BOT_TOKEN'),
            'http_template' => 'reliable',
            'http_overrides' => [
                'timeout' => 90, // 营销消息允许更长等待时间
            ],
            'webhook_url' => env('TELEGRAM_MARKETING_WEBHOOK_URL'),
            'webhook_secret' => env('TELEGRAM_MARKETING_WEBHOOK_SECRET'),
            'middleware' => ['auth', 'throttle'],
            'rate_limit' => [
                'enabled' => true,
                'max_requests' => 10,
                'per_seconds' => 60,
            ],
        ],

        // 开发测试机器人 - 使用调试模板
        'dev-test' => [
            'token' => env('TELEGRAM_DEV_BOT_TOKEN'),
            'http_template' => 'debug',
            'http_overrides' => [
                // 开发环境可能需要特殊配置
            ],
            'webhook_url' => env('TELEGRAM_DEV_WEBHOOK_URL'),
            'middleware' => ['dev_logger'],
            'logging' => [
                'enabled' => true,
                'level' => 'debug',
                'channel' => 'telegram-dev',
            ],
        ],

        // 生产环境机器人 - 使用生产模板
        'production' => [
            'token' => env('TELEGRAM_PROD_BOT_TOKEN'),
            'http_template' => 'production',
            'webhook_url' => env('TELEGRAM_PROD_WEBHOOK_URL'),
            'webhook_secret' => env('TELEGRAM_PROD_WEBHOOK_SECRET'),
            'middleware' => ['auth', 'security', 'rate_limit'],
            'rate_limit' => [
                'enabled' => true,
                'max_requests' => 20,
                'per_seconds' => 60,
            ],
        ],
        
        // 管理员机器人 - 使用生产模板
        'admin' => [
            'token' => env('TELEGRAM_ADMIN_BOT_TOKEN'),
            'http_template' => 'production',
            'http_overrides' => [
                'timeout' => 45,
            ],
            'webhook_url' => env('TELEGRAM_ADMIN_WEBHOOK_URL'),
            'webhook_secret' => env('TELEGRAM_ADMIN_WEBHOOK_SECRET'),
            'middleware' => ['auth', 'admin', 'ip_whitelist'],
            'rate_limit' => [
                'enabled' => true,
                'max_requests' => 50,
                'per_seconds' => 60,
            ],
            'logging' => [
                'enabled' => true,
                'level' => 'debug',
                'channel' => 'telegram-admin',
            ],
            'security' => [
                'ip_whitelist' => array_filter(explode(',', env('TELEGRAM_ADMIN_IP_WHITELIST', ''))),
                'require_https' => env('TELEGRAM_ADMIN_REQUIRE_HTTPS', true),
            ],
        ],
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
