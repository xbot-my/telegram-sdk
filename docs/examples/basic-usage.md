# 🔰 基础使用（极简）

本文档展示最小可用的用法，SDK 专注与 Telegram 服务器交互，默认返回数组。

## 🚀 快速开始

```php
<?php
require_once 'vendor/autoload.php';

use XBot\Telegram\Bot;
use XBot\Telegram\Models\Response\ResponseFormat as F;

$bot = Bot::token('YOUR_BOT_TOKEN');

// 数组（默认）
$me = $bot->getMe();

// 可选格式
$meObj  = $bot->as(F::OBJECT)->getMe();
$meJson = $bot->as(F::JSON)->getMe();

// 发送消息
$bot->sendMessage(123456789, 'Hello, World!');

// Chat 分组
$chat = $bot->chat()->getChat(123456789);
```

## 📝 常用选项

```php
$bot->sendMessage(123456789, '<b>HTML</b>', [
    'parse_mode' => 'HTML',
    'disable_notification' => true,
]);
```

## 🔄 处理更新（长轮询）

```php
$updates = $bot->getUpdates(['limit' => 10]);
foreach ($updates as $update) {
    if (isset($update['message'])) {
        $message = $update['message'];
        $chatId = $message['chat']['id'];
        $text = $message['text'] ?? '';
        $bot->sendMessage($chatId, "你说了: $text");
    }
}
```

## 🌐 Webhook 辅助

```php
$bot->setWebhook('https://example.com/telegram/webhook');
$info = $bot->getWebhookInfo();
$bot->deleteWebhook(true);
```

提示：`collection` 输出需要安装 `illuminate/support`。
