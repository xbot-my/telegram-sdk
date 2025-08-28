# Telegram Bot PHP SDK

一个功能强大、易于使用的 PHP Telegram Bot API SDK，支持多 Token、多 Bot、多实例管理，实例间完全隔离互不干扰。

## ✨ 特性

- 🤖 **多 Bot 支持** - 支持同时管理多个 Bot 实例
- 🔒 **实例隔离** - 每个 Bot 实例完全独立，互不干扰
- ⚡ **高性能** - 基于 Guzzle HTTP 客户端，支持连接池和异步请求
- 🎯 **类型安全** - 完整的 DTO 模型和类型提示
- 🛡️ **异常处理** - 完善的异常处理体系
- 🔄 **重试机制** - 内置智能重试和错误恢复
- 📊 **统计监控** - 详细的调用统计和性能监控
- 🌐 **Laravel 集成** - 开箱即用的 Laravel 支持
- 🎨 **链式调用** - 优雅的 API 设计
- 📝 **完整文档** - 详细的使用文档和示例

## 📦 安装

使用 Composer 安装：

```bash
composer require xbot-my/telegram-sdk
```

### Laravel 集成

发布配置文件：

```bash
php artisan vendor:publish --provider="XBot\Telegram\Providers\TelegramServiceProvider"
```

## 🚀 快速开始

### 一行快速发送（Bot 入口）

```php
use XBot\Telegram\Bot;

Bot::init([
    'default' => 'main',
    'bots' => [
        'main' => ['token' => 'YOUR_BOT_TOKEN']
    ],
]);

Bot::to(123456789)->html()->message('<b>Hello</b>');
// 指定 Bot
Bot::via('marketing')->to(123456789)->message('Hi');
```

### 基础使用

```php
use XBot\Telegram\BotManager;
use XBot\Telegram\Http\HttpClientConfig;
use XBot\Telegram\Http\GuzzleHttpClient;
use XBot\Telegram\TelegramBot;

// 创建单个 Bot 实例
$config = HttpClientConfig::fromArray([
    'token' => 'YOUR_BOT_TOKEN',
    'timeout' => 30,
]);

$httpClient = new GuzzleHttpClient($config);
$bot = new TelegramBot('my-bot', $httpClient);

// 发送消息
$message = $bot->sendMessage(
    chatId: 12345,
    text: 'Hello, World!'
);

echo "Message sent! ID: {$message->messageId}";
```

### 多 Bot 管理

```php
use XBot\Telegram\BotManager;

// 配置多个 Bot
$config = [
    'default' => 'main',
    'bots' => [
        'main' => [
            'token' => 'MAIN_BOT_TOKEN',
            'timeout' => 30,
        ],
        'customer-service' => [
            'token' => 'CS_BOT_TOKEN',
            'timeout' => 15,
        ],
        'marketing' => [
            'token' => 'MARKETING_BOT_TOKEN',
            'timeout' => 60,
        ],
    ],
];

$manager = new BotManager($config);

// 使用默认 Bot
$mainBot = $manager->bot();
$mainBot->sendMessage(12345, 'Main bot message');

// 使用指定 Bot
$csBot = $manager->bot('customer-service');
$csBot->sendMessage(12345, 'Customer service reply');

$marketingBot = $manager->bot('marketing');
$marketingBot->sendMessage(12345, 'Marketing campaign');
```

### Laravel Facade 使用

```php
use XBot\Telegram\Facades\Telegram;

// 使用默认 Bot
Telegram::sendMessage(12345, 'Hello from Laravel!');

// 使用指定 Bot
Telegram::bot('customer-service')->sendMessage(12345, 'CS message');

// 链式调用
Telegram::to(12345)
    ->html()
    ->keyboard([
        [['text' => 'Button 1', 'callback_data' => 'btn1']],
        [['text' => 'Button 2', 'callback_data' => 'btn2']]
    ])
    ->message('<b>Choose an option:</b>');

// 使用指定 Bot 的链式调用
Telegram::via('marketing')
    ->to(12345)
    ->markdown()
    ->silent()
    ->message('*Marketing message*');
```

## 📋 配置

### Laravel 配置文件 (`config/telegram.php`)

```php
return [
    // 默认 Bot 名称
    'default' => env('TELEGRAM_DEFAULT_BOT', 'main'),

    // 多 Bot 配置
    'bots' => [
        'main' => [
            'token' => env('TELEGRAM_MAIN_BOT_TOKEN'),
            'base_url' => env('TELEGRAM_BASE_URL', 'https://api.telegram.org/bot'),
            'timeout' => (int) env('TELEGRAM_TIMEOUT', 30),
            'retry_attempts' => (int) env('TELEGRAM_RETRY_ATTEMPTS', 3),
            'retry_delay' => (int) env('TELEGRAM_RETRY_DELAY', 1000),
            'webhook_url' => env('TELEGRAM_MAIN_WEBHOOK_URL'),
            'webhook_secret' => env('TELEGRAM_MAIN_WEBHOOK_SECRET'),
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
            // ... 其他配置
        ],

        'marketing' => [
            'token' => env('TELEGRAM_MARKETING_BOT_TOKEN'),
            // ... 其他配置
        ],
    ],

    // Webhook 配置
    'webhook' => [
        'route_prefix' => 'telegram/webhook',
        'middleware' => ['api', 'telegram.webhook'],
        'verify_signature' => true,
    ],
];
```

### 环境变量 (`.env`)

```env
# 主 Bot 配置
TELEGRAM_DEFAULT_BOT=main
TELEGRAM_MAIN_BOT_TOKEN=123456789:AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA
TELEGRAM_MAIN_WEBHOOK_URL=https://yourapp.com/telegram/webhook/main
TELEGRAM_MAIN_WEBHOOK_SECRET=your-webhook-secret

# 客服 Bot 配置
TELEGRAM_CS_BOT_TOKEN=987654321:BBBBBBBBBBBBBBBBBBBBBBBBBBBBBBB
TELEGRAM_CS_WEBHOOK_URL=https://yourapp.com/telegram/webhook/customer-service

# 营销 Bot 配置  
TELEGRAM_MARKETING_BOT_TOKEN=555555555:CCCCCCCCCCCCCCCCCCCCCCCCCCCCCCC
TELEGRAM_MARKETING_WEBHOOK_URL=https://yourapp.com/telegram/webhook/marketing

# 全局配置
TELEGRAM_TIMEOUT=30
TELEGRAM_RETRY_ATTEMPTS=3
TELEGRAM_LOGGING_ENABLED=true
```

## 🎯 API 使用示例

### 消息发送

```php
// 文本消息
$message = $bot->sendMessage(12345, 'Hello!');

// 带格式的消息
$message = $bot->sendMessage(12345, '<b>Bold</b> and <i>italic</i>', [
    'parse_mode' => 'HTML',
]);

// 带键盘的消息
$message = $bot->sendMessage(12345, 'Choose an option:', [
    'reply_markup' => [
        'inline_keyboard' => [
            [['text' => 'Option 1', 'callback_data' => 'opt1']],
            [['text' => 'Option 2', 'callback_data' => 'opt2']],
        ]
    ]
]);
```

### 媒体发送

```php
// 发送照片
$message = $bot->sendPhoto(12345, '/path/to/photo.jpg', [
    'caption' => 'Beautiful photo!',
]);

// 发送文档
$message = $bot->sendDocument(12345, '/path/to/document.pdf');

// 发送语音
$message = $bot->sendVoice(12345, '/path/to/voice.ogg');
```

### 聊天管理

```php
// 获取聊天信息
$chat = $bot->getChat(-100123456789);

// 获取聊天成员
$member = $bot->getChatMember(-100123456789, 12345);

// 封禁用户
$success = $bot->banChatMember(-100123456789, 12345);

// 设置聊天标题
$success = $bot->setChatTitle(-100123456789, 'New Chat Title');
```

### Webhook 处理

```php
// 在控制器中处理 Webhook
class TelegramWebhookController extends Controller
{
    public function handle(Request $request, string $botName)
    {
        $bot = app('telegram')->bot($botName);
        
        // 获取更新数据
        $update = Update::fromArray($request->all());
        
        if ($update->isMessage()) {
            $message = $update->message;
            
            // 回复消息
            $bot->sendMessage(
                $message->chat->id,
                "You said: {$message->text}"
            );
        }
        
        return response()->json(['ok' => true]);
    }
}
```

## 🔧 Artisan 命令

### 查看 Bot 信息

```bash
# 查看默认 Bot 信息
php artisan telegram:info

# 查看指定 Bot 信息
php artisan telegram:info customer-service

# 查看所有 Bot 信息
php artisan telegram:info --all

# JSON 格式输出
php artisan telegram:info --json
```

### Webhook 管理

```bash
# 设置 Webhook
php artisan telegram:webhook set --url=https://yourapp.com/webhook

# 设置指定 Bot 的 Webhook
php artisan telegram:webhook set customer-service --url=https://yourapp.com/webhook/cs

# 设置所有 Bot 的 Webhook
php artisan telegram:webhook set --url=https://yourapp.com/webhook --all

# 删除 Webhook
php artisan telegram:webhook delete

# 查看 Webhook 信息
php artisan telegram:webhook info --all
```

### 健康检查

```bash
# 检查默认 Bot
php artisan telegram:health

# 检查所有 Bot
php artisan telegram:health --all

# JSON 格式输出
php artisan telegram:health --all --json
```

### 统计信息

```bash
# 查看 Bot 统计
php artisan telegram:stats

# 查看所有 Bot 统计
php artisan telegram:stats --all

# 重置统计信息
php artisan telegram:stats --reset
```

## 🛡️ 异常处理

SDK 提供了完善的异常处理体系：

```php
use XBot\Telegram\Exceptions\{ApiException,HttpException,TelegramException,\InstanceException,ValidationException};

try {
    $bot->sendMessage(12345, 'Hello!');
} catch (ApiException $e) {
    // Telegram API 错误
    echo "API Error: " . $e->getDescription();
    
    if ($e->isRateLimited()) {
        $retryAfter = $e->getRetryAfter();
        echo "Rate limited, retry after {$retryAfter} seconds";
    }
} catch (HttpException $e) {
    // HTTP 请求错误
    echo "HTTP Error: " . $e->getMessage();
    
    if ($e->isTimeout()) {
        echo "Request timed out";
    }
} catch (ValidationException $e) {
    // 参数验证错误
    echo "Validation Error: " . $e->getMessage();
    print_r($e->getErrors());
} catch (TelegramException $e) {
    // 其他 Telegram 相关错误
    echo "Telegram Error: " . $e->getMessage();
}
```

## 📊 监控和统计

### Bot 统计信息

```php
$bot = $manager->bot('main');
$stats = $bot->getStats();

/*
Array (
    [name] => main
    [total_calls] => 150
    [successful_calls] => 148
    [failed_calls] => 2
    [success_rate] => 98.67
    [uptime] => 3600
    [uptime_formatted] => 1h 0m 0s
    [last_call_time] => 1640995200
    [http_client_stats] => Array (
        [total_requests] => 150
        [successful_requests] => 148
        [failed_requests] => 2
        [retry_count] => 3
        [average_time] => 0.245
    )
)
*/
```

### 管理器统计信息

```php
$managerStats = $manager->getStats();

/*
Array (
    [default_bot] => main
    [total_bots_configured] => 3
    [total_bots_loaded] => 2
    [total_bots_created] => 5
    [total_bots_removed] => 1
    [total_reload_count] => 2
    [uptime] => 7200
    [uptime_formatted] => 2h 0m 0s
    [memory_usage] => 8388608
    [memory_peak] => 12582912
)
*/
```

### 健康检查

```php
$healthResults = $manager->healthCheck();

/*
Array (
    [main] => Array (
        [name] => main
        [is_loaded] => true
        [is_healthy] => true
        [response_time] => 245.5
        [error] => null
    )
    [customer-service] => Array (
        [name] => customer-service
        [is_loaded] => true
        [is_healthy] => false
        [response_time] => 1250.2
        [error] => Connection timeout
    )
)
*/
```

## 🔒 安全特性

### Webhook 签名验证

```php
// 在配置中启用签名验证
'webhook' => [
    'verify_signature' => true,
],

// 为每个 Bot 配置密钥
'bots' => [
    'main' => [
        'webhook_secret' => 'your-secret-token',
    ],
],
```

### 速率限制

```php
// 为每个 Bot 配置速率限制
'bots' => [
    'main' => [
        'rate_limit' => [
            'enabled' => true,
            'max_requests' => 30,
            'per_seconds' => 60,
        ],
    ],
],
```

## 🧪 测试

运行测试套件：

```bash
# 运行所有测试
./vendor/bin/pest

# 运行指定测试
./vendor/bin/pest tests/Unit/BotManagerTest.php

# 运行测试并生成覆盖率报告
./vendor/bin/pest --coverage
```

## 📈 性能优化

### 实例复用

```php
// 好的做法：复用实例
$bot = $manager->bot('main');
for ($i = 0; $i < 100; $i++) {
    $bot->sendMessage($chatId, "Message {$i}");
}

// 避免：每次都创建新实例
for ($i = 0; $i < 100; $i++) {
    $manager->bot('main')->sendMessage($chatId, "Message {$i}");
}
```

### 连接池配置

```php
'bots' => [
    'main' => [
        'token' => 'YOUR_TOKEN',
        'timeout' => 30,
        'connect_timeout' => 10,
        'max_redirects' => 5,
        // 使用 HTTP/2 for better performance
        'headers' => [
            'Connection' => 'keep-alive',
        ],
    ],
],
```

## 🤝 贡献

欢迎贡献代码！请查看 [CONTRIBUTING.md](CONTRIBUTING.md) 了解详细信息。

## 📄 许可证

本项目采用 [MIT 许可证](LICENSE)。

## 🆘 支持

- 📖 [完整文档](https://github.com/xbot-my/telegram-sdk/docs)
- 🐛 [问题报告](https://github.com/xbot-my/telegram-sdk/issues)
- 💬 [讨论区](https://github.com/xbot-my/telegram-sdk/discussions)
- 📧 Email: admin@xbot.my

## 🎉 致谢

感谢所有为这个项目做出贡献的开发者们！

---

**Happy Bot Building! 🤖✨**
## 🧭 基础接口速查表

- 发送消息: `sendMessage(chatId, text, options)` — 支持 `parse_mode`, `disable_notification`, `reply_markup`
- 编辑/删除: `editMessageText(chatId, messageId, text, options)`, `deleteMessage(chatId, messageId)`
  - 其他编辑: `editMessageCaption(chatId, messageId, caption, options)`, `editMessageReplyMarkup(chatId, messageId, replyMarkup)`
- 转发/复制: `forwardMessage(chatId, fromChatId, messageId)`, `copyMessage(chatId, fromChatId, messageId)`
- 媒体发送: `sendPhoto|Video|Audio|Document|Voice|Animation(chatId, file, options)` — 本地文件自动走 `upload`
- 位置/联系人/投票: `sendLocation(lat, lon)`, `sendContact(phone, firstName)`, `sendPoll(question, options, settings)`
- 更新/Webhook: `getUpdates(options)`, `setWebhook(url, options)`, `deleteWebhook(dropPending)`, `getWebhookInfo()`
- 聊天与成员: `getChat(chatId)`, `getChatMember(chatId, userId)`, `getChatMemberCount(chatId)`
- 管理: `banChatMember|unbanChatMember|restrictChatMember|promoteChatMember(chatId, userId, options)`
 - 文件与头像: `getFile(fileId)`, `getUserProfilePhotos(userId, options)`
 - 聊天基础：`getChatAdministrators(chatId)`；`setChatTitle/Description`；`setChatPhoto/deleteChatPhoto`；`pinChatMessage/unpinChatMessage/unpinAllChatMessages`；`leaveChat`
 - 键盘：内联 `inline_keyboard`、回复 `keyboard`、`remove_keyboard`、`force_reply`
   - Builder: `InlineKeyboardBuilder` / `ReplyKeyboardBuilder`
 - 命令：`setMyCommands(commands, options)` / `getMyCommands()` / `deleteMyCommands()`

示例（Bot 入口）：`Bot::to(123)->markdown()->message('*Hello*')`

## 🧩 Webhook 部署与排错
- 部署
  - 路由：`POST /{prefix}/{botName}`，默认前缀 `telegram/webhook`。
  - 注册：`setWebhook('https://your.app/telegram/webhook/main', ['secret_token' => '...'])`。
- 常见问题
  - 非 HTTPS：Telegram 要求 HTTPS，HTTP 会被拒绝。
  - 403/签名失败：确保 `secret_token` 与服务端验证一致（`telegram.webhook` 中间件）。
  - 429：降低速率或设置 `max_connections`，并考虑 `deleteWebhook(true)` 清理积压。
  - 内网不可达：对外需可访问，必要时通过反向代理或隧道（如 Cloudflare Tunnel）。
