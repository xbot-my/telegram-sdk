<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Single Bot Token
    |--------------------------------------------------------------------------
    |
    | 单实例机器人 token。若你使用多实例，请自行扩展配置结构。
    |
    */
    'token'            => env('TELEGRAM_BOT_TOKEN'),
    'name'             => env('TELEGRAM_BOT_NAME', 'default'),

    /*
    |--------------------------------------------------------------------------
    | HTTP Client
    |--------------------------------------------------------------------------
    |
    | 单实例的 HTTP 客户端默认配置。可通过 .env 覆盖。
    |
    */
    'http'             => [
        'base_url'        => env('TELEGRAM_BASE_URL', 'https://api.telegram.org/bot'),
        'timeout'         => (int)env('TELEGRAM_TIMEOUT', 30),
        'connect_timeout' => (int)env('TELEGRAM_CONNECT_TIMEOUT', 10),
        'read_timeout'    => (int)env('TELEGRAM_READ_TIMEOUT', 30),
        'retry_attempts'  => (int)env('TELEGRAM_RETRY_ATTEMPTS', 3),
        'retry_delay'     => (int)env('TELEGRAM_RETRY_DELAY', 1000),
        'verify_ssl'      => (bool)env('TELEGRAM_VERIFY_SSL', true),
        'user_agent'      => env('TELEGRAM_USER_AGENT', 'XBot-Telegram-SDK/1.0'),
        'max_redirects'   => (int)env('TELEGRAM_MAX_REDIRECTS', 5),
        'debug'           => (bool)env('TELEGRAM_DEBUG', false),

        /*
        |--------------------------------------------------------------------------
        | Logging
        |--------------------------------------------------------------------------
        |
        |
        |
        */
        'logging'         => [
            'enabled'       => (bool)env('TELEGRAM_LOG_ENABLED', true),
            'suppress_info' => (bool)env('TELEGRAM_LOG_SUPPRESS_INFO', false),
            'channel'       => env('TELEGRAM_LOG_CHANNEL'),
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
        // Use SDK default unless overridden
        'pattern' => env('TELEGRAM_TOKEN_PATTERN'),
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
    // 说明：已放弃多实例配置，如需多实例请自行扩展。

    /*
    |--------------------------------------------------------------------------
    | Webhook Configuration
    |--------------------------------------------------------------------------
    |
    | Webhook 配置
    |
    */
    'webhook'          => [
        'route_prefix'     => env('TELEGRAM_WEBHOOK_ROUTE_PREFIX', 'telegram/webhook'),
        'middleware'       => ['api', 'telegram.webhook'],
        'verify_signature' => env('TELEGRAM_WEBHOOK_VERIFY_SIGNATURE', true),
        'secret_token'     => env('TELEGRAM_WEBHOOK_SECRET'),
        // Optionally register handler class names here; they will be resolved and called per update
        'handlers'         => [
            // App\Telegram\Handlers\MyHandler::class,
        ],
        'max_connections'  => (int)env('TELEGRAM_WEBHOOK_MAX_CONNECTIONS', 100),
        'allowed_updates'  => [
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
