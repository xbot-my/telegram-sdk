# 🚀 快速开始

欢迎使用 Telegram Bot PHP SDK！本指南将帮助您在 5 分钟内创建并运行第一个 Telegram Bot。

## 📋 前置条件

在开始之前，请确保您已经：

- ✅ 安装了 PHP 8.3 或更高版本
- ✅ 安装了 Composer
- ✅ 拥有一个 Telegram Bot Token（[如何获取](installation.md#获取-bot-token)）

## 🏃 5 分钟快速体验

### 第 1 步：安装 SDK

```bash
composer require xbot-my/telegram-sdk
```

### 第 2 步：用 `Bot` 入口一行发送

创建文件 `my-first-bot.php`：

```php
<?php

require_once 'vendor/autoload.php';

use XBot\Telegram\Bot;

// 初始化（可同时配置多个 Bot）
Bot::init([
    'default' => 'main',
    'bots' => [
        'main' => ['token' => '123456789:AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA'],
        // 可选：更多 Bot
        // 'marketing' => ['token' => '...'],
    ],
]);

// 一行发送（默认 Bot）
Bot::to(123456789)->html()->message('<b>Hello, World!</b>');

// 指定 Bot 发送
// Bot::via('marketing')->to(123456789)->markdown()->message('*Hi*');
```

### 第 3 步：运行脚本

```bash
php my-first-bot.php
```

如果一切正常，您应该看到类似输出：
```
🤖 Bot 已连接: @my_awesome_bot
📝 Bot 名称: My Awesome Bot
✅ 消息发送成功，消息ID: 123
```

> 💡 **如何获取聊天 ID**：
> 1. 向您的 Bot 发送任意消息
> 2. 访问 `https://api.telegram.org/bot<YOUR_TOKEN>/getUpdates`
> 3. 在响应中查找 `chat.id` 字段

## 🔄 创建交互式 Bot

让我们创建一个能够响应用户消息的 Bot：

### 第 4 步：处理用户消息

创建文件 `interactive-bot.php`：

```php
<?php

require_once 'vendor/autoload.php';

use XBot\Telegram\Bot;
use XBot\Telegram\Models\DTO\Update;

Bot::init([
    'default' => 'main',
    'bots' => [
        'main' => ['token' => 'YOUR_BOT_TOKEN'],
    ],
]);

$bot = Bot::bot(); // 默认 Bot 实例

// 获取更新
$updates = $bot->getUpdates(['limit' => 10]);

foreach ($updates as $updateData) {
    $update = Update::fromArray($updateData);
    
    if ($update->isMessage()) {
        $message = $update->message;
        $chatId = $message->chat->id;
        $text = $message->text ?? '';
        
        // 处理不同命令
        switch ($text) {
            case '/start':
                $bot->sendMessage($chatId, "👋 欢迎！我是您的智能助手。\n\n" .
                    "可用命令：\n" .
                    "/help - 查看帮助\n" .
                    "/time - 获取当前时间\n" .
                    "/joke - 听个笑话");
                break;
                
            case '/help':
                $bot->sendMessage($chatId, "🤔 需要帮助吗？\n\n" .
                    "这是一个演示 Bot，支持以下功能：\n" .
                    "• 时间查询\n" .
                    "• 随机笑话\n" .
                    "• 文本回显");
                break;
                
            case '/time':
                $currentTime = date('Y-m-d H:i:s');
                $bot->sendMessage($chatId, "🕐 当前时间：$currentTime");
                break;
                
            case '/joke':
                $jokes = [
                    "为什么程序员不喜欢自然？因为自然有太多的bug！",
                    "为什么Java程序员要戴眼镜？因为他们看不到C#！",
                    "程序员的梦想是什么？没有bug的代码！"
                ];
                $joke = $jokes[array_rand($jokes)];
                $bot->sendMessage($chatId, "😄 $joke");
                break;
                
            default:
                if (!empty($text)) {
                    $bot->sendMessage($chatId, "📢 你说：$text\n\n" .
                        "输入 /help 查看可用命令。");
                }
                break;
        }
    }
}

echo "✅ 处理完成\n";
```

### 第 5 步：运行交互式 Bot

```bash
php interactive-bot.php
```

现在向您的 Bot 发送 `/start` 命令试试！

## 🏗️ Laravel 集成

如果您使用 Laravel，集成更加简单：

### 第 6 步：Laravel 配置

1. **发布配置文件**：
```bash
php artisan vendor:publish --provider="XBot\Telegram\Providers\TelegramServiceProvider"
```

2. **配置环境变量**（`.env`）：
```env
TELEGRAM_MAIN_BOT_TOKEN=YOUR_BOT_TOKEN
TELEGRAM_MAIN_WEBHOOK_URL=https://yourapp.com/telegram/webhook/main
```

### 第 7 步：使用 Facade

创建控制器 `app/Http/Controllers/TelegramController.php`：

```php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use XBot\Telegram\Facades\Telegram;

class TelegramController extends Controller
{
    public function sendWelcome(Request $request)
    {
        $chatId = $request->input('chat_id');
        
        // 使用 Facade 发送消息
        $message = Telegram::sendMessage($chatId, '🎉 欢迎使用 Laravel + Telegram SDK！');
        
        return response()->json([
            'success' => true,
            'message_id' => $message->messageId,
            'text' => $message->text
        ]);
    }
    
    public function handleWebhook(Request $request)
    {
        $update = $request->all();
        
        if (isset($update['message'])) {
            $chatId = $update['message']['chat']['id'];
            $text = $update['message']['text'] ?? '';
            
            if ($text === '/start') {
                Telegram::sendMessage($chatId, '👋 Hello from Laravel!');
            } else {
                Telegram::sendMessage($chatId, "Echo: $text");
            }
        }
        
        return response()->json(['ok' => true]);
    }
}
```

### 第 8 步：设置路由

在 `routes/web.php` 中添加：

```php
use App\Http\Controllers\TelegramController;

Route::post('/telegram/webhook', [TelegramController::class, 'handleWebhook']);
Route::post('/telegram/send-welcome', [TelegramController::class, 'sendWelcome']);
```

## 🔗 设置 Webhook

为了实时接收消息，建议设置 Webhook：

### 第 9 步：设置 Webhook

```php
<?php

require_once 'vendor/autoload.php';

use XBot\Telegram\Bot;

Bot::init([
    'default' => 'main',
    'bots' => [
        'main' => ['token' => 'YOUR_BOT_TOKEN'],
    ],
]);

$bot = Bot::bot();

// 设置 Webhook
$result = $bot->setWebhook($webhookUrl);

if ($result) {
    echo "✅ Webhook 设置成功：$webhookUrl\n";
} else {
    echo "❌ Webhook 设置失败\n";
}

// 查看 Webhook 信息
$webhookInfo = $bot->getWebhookInfo();
echo "📡 当前 Webhook：{$webhookInfo['url']}\n";
```

## 📊 发送丰富消息

让我们尝试发送一些丰富的消息格式：

### 第 10 步：发送带键盘的消息

```php
<?php

require_once 'vendor/autoload.php';

use XBot\Telegram\Bot;

Bot::init([
    'default' => 'main',
    'bots' => [
        'main' => ['token' => 'YOUR_BOT_TOKEN'],
    ],
]);

$bot = Bot::bot();

// 1. 发送带内联键盘的消息
$bot->sendMessage($chatId, '🎮 选择您的操作：', [
    'reply_markup' => [
        'inline_keyboard' => [
            [
                ['text' => '🎯 选项 1', 'callback_data' => 'option_1'],
                ['text' => '🚀 选项 2', 'callback_data' => 'option_2']
            ],
            [
                ['text' => '🔗 访问网站', 'url' => 'https://github.com/xbot-my/telegram-sdk']
            ]
        ]
    ]
]);

// 2. 发送带格式的消息
$bot->sendMessage($chatId, 
    "<b>粗体文本</b>\n" .
    "<i>斜体文本</i>\n" .
    "<code>代码文本</code>\n" .
    "<pre>预格式化文本</pre>\n" .
    "<a href='https://telegram.org'>链接文本</a>",
    ['parse_mode' => 'HTML']
);

// 3. 发送 Markdown 格式消息
$bot->sendMessage($chatId,
    "*粗体* 和 _斜体_\n" .
    "`代码` 和 ```\n预格式化代码块\n```\n" .
    "[链接](https://telegram.org)",
    ['parse_mode' => 'MarkdownV2']
);

echo "✅ 丰富消息发送完成！\n";
```

## 🎯 实用技巧

### 1. 错误处理

```php
use XBot\Telegram\Exceptions\ApiException;
use XBot\Telegram\Exceptions\HttpException;

try {
    $message = $bot->sendMessage($chatId, 'Hello!');
} catch (ApiException $e) {
    echo "API 错误: " . $e->getDescription() . "\n";
    echo "错误代码: " . $e->getErrorCode() . "\n";
} catch (HttpException $e) {
    echo "网络错误: " . $e->getMessage() . "\n";
} catch (Exception $e) {
    echo "其他错误: " . $e->getMessage() . "\n";
}
```

### 2. 批量操作

```php
// 批量发送消息
$chatIds = [123456789, 987654321, 555666777];
$message = '📢 重要通知：系统将于今晚维护！';

foreach ($chatIds as $chatId) {
    try {
        $bot->sendMessage($chatId, $message);
        echo "✅ 消息已发送到: $chatId\n";
        
        // 避免触发速率限制
        usleep(100000); // 0.1 秒延迟
    } catch (Exception $e) {
        echo "❌ 发送失败到 $chatId: " . $e->getMessage() . "\n";
    }
}
```

### 3. 消息模板

```php
class MessageTemplates 
{
    public static function welcome($username): string
    {
        return "👋 欢迎，{$username}！\n\n" .
               "感谢您使用我们的服务。如需帮助，请输入 /help";
    }
    
    public static function help(): string
    {
        return "🤖 可用命令：\n\n" .
               "/start - 开始使用\n" .
               "/help - 显示帮助\n" .
               "/settings - 设置选项\n" .
               "/contact - 联系客服";
    }
    
    public static function error($errorCode): string
    {
        return "❌ 出现错误 (代码: {$errorCode})\n\n" .
               "请稍后重试，或联系客服获取帮助。";
    }
}

// 使用模板
$bot->sendMessage($chatId, MessageTemplates::welcome('张三'));
```

## 🔧 调试技巧

### 启用调试模式

```php
// 在开发环境中启用详细日志
$httpClient = new GuzzleHttpClient($token, [
    'debug' => true,
    'timeout' => 30
]);
```

### 查看原始响应

```php
// 获取原始 API 响应
$response = $bot->call('getMe', []);
echo "原始响应：\n";
print_r($response->getRawData());
```

## ➡️ 下一步

恭喜！您已经成功创建了第一个 Telegram Bot。接下来您可以：

1. 📖 深入了解 [配置选项](configuration.md)
2. 🔍 浏览 [API 参考文档](../api/)
3. 💡 查看 [使用示例](../examples/)
4. ⭐ 学习 [最佳实践](../best-practices/)

## 🆘 需要帮助？

- 📋 查看 [常见问题](../troubleshooting/common-issues.md)
- 🐛 [提交 Issue](https://github.com/xbot-my/telegram-sdk/issues)
- 💬 参与 [讨论](https://github.com/xbot-my/telegram-sdk/discussions)

---

🎉 **恭喜您完成快速入门！** 现在您已经掌握了 Telegram Bot PHP SDK 的基础使用方法。
