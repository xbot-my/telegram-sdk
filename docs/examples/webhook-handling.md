# 🔗 Webhook 处理

本文展示如何使用本 SDK 处理 Telegram Webhook，包括纯 PHP 与 Laravel 场景。

## 1) 纯 PHP 示例

```php
<?php

require_once 'vendor/autoload.php';

use XBot\\Telegram\\Bot;
use XBot\\Telegram\\Models\\DTO\\Update;

// 初始化 Bot
Bot::init([
  'default' => 'main',
  'bots' => [
    'main' => ['token' => 'YOUR_BOT_TOKEN'],
  ],
]);

// 读取原始请求体
$raw = file_get_contents('php://input');
$data = json_decode($raw, true) ?: [];

// 构建 Update 对象
$update = Update::fromArray($data);
$bot = Bot::bot();

if ($update->isMessage()) {
  $msg = $update->message;
  $chatId = $msg->chat->id;
  $text = $msg->text ?? '';

  if ($text === '/start') {
    $bot->sendMessage($chatId, '👋 欢迎！');
  } else {
    $bot->sendMessage($chatId, "你说了: $text");
  }
}

http_response_code(200);
echo json_encode(['ok' => true]);
```

设置 Webhook：

```php
<?php

require_once 'vendor/autoload.php';

use XBot\\Telegram\\Bot;

Bot::init(['default' => 'main','bots' => ['main' => ['token' => 'YOUR_BOT_TOKEN']]]);
Bot::bot()->setWebhook('https://your-domain.com/path/to/this-script.php');
```

## 2) Laravel 示例

路由：

```php
// routes/api.php
use App\\Http\\Controllers\\TelegramWebhookController;

Route::post('telegram/webhook/{botName}', [TelegramWebhookController::class, 'handle']);
```

控制器：

```php
<?php

namespace App\\Http\\Controllers;

use Illuminate\\Http\\Request;
use XBot\\Telegram\\Bot;
use XBot\\Telegram\\Models\\DTO\\Update;

class TelegramWebhookController extends Controller
{
    public function handle(Request $request, string $botName = 'main')
    {
        // 初始化（生产中可在服务提供者中全局初始化）
        if (! app()->bound('telegram.bot.initialized')) {
            \XBot\\Telegram\\Bot::init([
                'default' => 'main',
                'bots' => [
                    'main' => ['token' => env('TELEGRAM_MAIN_BOT_TOKEN')],
                ],
            ]);
            app()->instance('telegram.bot.initialized', true);
        }

        $bot = Bot::bot($botName);
        $update = Update::fromArray($request->all());

        if ($update->isMessage()) {
            $chatId = $update->message->chat->id;
            $text = $update->message->text ?? '';

            $bot->sendMessage($chatId, $text === '/start' ? '👋 欢迎！' : "你说了: $text");
        }

        return response()->json(['ok' => true]);
    }
}
```

设置 Webhook：

```php
use XBot\\Telegram\\Bot;

Bot::init(['default' => 'main','bots' => ['main' => ['token' => env('TELEGRAM_MAIN_BOT_TOKEN')]]]);
Bot::bot()->setWebhook(config('app.url').'/api/telegram/webhook/main');
```

> 提示：生产环境请使用 HTTPS，并为不同 Bot 使用不同 Webhook 路径；如启用签名校验，请确保服务端与设置 Webhook 时的 `secret_token` 一致。
