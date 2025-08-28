# 🔰 基础使用

本文档介绍 Telegram Bot PHP SDK 的基础使用方法和常见场景。

## 🚀 快速开始

### 1. 创建第一个 Bot

```php
<?php

require_once 'vendor/autoload.php';

use XBot\Telegram\BotManager;
use XBot\Telegram\Http\GuzzleHttpClient;

// 创建 HTTP 客户端
$httpClient = new GuzzleHttpClient('YOUR_BOT_TOKEN');

// 创建 Bot 管理器
$manager = new BotManager();

// 创建 Bot 实例
$bot = $manager->createBot('my-bot', $httpClient);

// 发送第一条消息
$message = $bot->sendMessage(123456789, 'Hello, World!');
echo "消息已发送，ID: " . $message->messageId;
```

## 📝 发送消息

### 基础文本消息

```php
// 简单文本消息
$bot->sendMessage($chatId, 'Hello!');

// 带格式的消息
$bot->sendMessage($chatId, '<b>粗体</b> 和 <i>斜体</i>', [
    'parse_mode' => 'HTML'
]);

// Markdown 格式
$bot->sendMessage($chatId, '*粗体* 和 _斜体_', [
    'parse_mode' => 'MarkdownV2'
]);
```

### 带键盘的消息

```php
// 内联键盘
$bot->sendMessage($chatId, '选择一个选项:', [
    'reply_markup' => [
        'inline_keyboard' => [
            [
                ['text' => '选项 1', 'callback_data' => 'option_1'],
                ['text' => '选项 2', 'callback_data' => 'option_2']
            ],
            [
                ['text' => '访问网站', 'url' => 'https://example.com']
            ]
        ]
    ]
]);

// 自定义键盘
$bot->sendMessage($chatId, '选择功能:', [
    'reply_markup' => [
        'keyboard' => [
            [['text' => '📊 统计'], ['text' => '⚙️ 设置']],
            [['text' => '📞 联系客服']]
        ],
        'resize_keyboard' => true,
        'one_time_keyboard' => true
    ]
]);
```

## 🖼️ 发送媒体

### 图片

```php
// 发送网络图片
$bot->sendPhoto($chatId, 'https://example.com/image.jpg', [
    'caption' => '这是一张图片'
]);

// 发送本地图片
$bot->sendPhoto($chatId, fopen('/path/to/image.jpg', 'r'), [
    'caption' => '本地图片'
]);
```

### 文档

```php
// 发送文档
$bot->sendDocument($chatId, fopen('/path/to/document.pdf', 'r'), [
    'caption' => 'PDF 文档'
]);
```

## 🔄 处理更新

### 使用 getUpdates

```php
// 获取更新
$updates = $bot->getUpdates(['limit' => 10]);

foreach ($updates as $updateData) {
    $update = Update::fromArray($updateData);
    
    if ($update->isMessage()) {
        $message = $update->message;
        $chatId = $message->chat->id;
        $text = $message->text;
        
        // 处理消息
        $bot->sendMessage($chatId, "你说了: $text");
    }
}
```

### 处理回调查询

```php
if ($update->isCallbackQuery()) {
    $callbackQuery = $update->callbackQuery;
    $data = $callbackQuery->data;
    $chatId = $callbackQuery->message->chat->id;
    
    // 答复回调查询
    $bot->answerCallbackQuery($callbackQuery->id, [
        'text' => '已收到您的选择!'
    ]);
    
    // 处理不同的回调数据
    switch ($data) {
        case 'option_1':
            $bot->sendMessage($chatId, '您选择了选项 1');
            break;
        case 'option_2':
            $bot->sendMessage($chatId, '您选择了选项 2');
            break;
    }
}
```

## 🔧 常用方法

### 获取 Bot 信息

```php
$botInfo = $bot->getMe();
echo "Bot 用户名: @{$botInfo->username}";
echo "Bot 名称: {$botInfo->firstName}";
```

### 编辑消息

```php
// 编辑消息文本
$bot->editMessageText($chatId, $messageId, '更新后的文本');

// 编辑消息键盘
$bot->editMessageReplyMarkup($chatId, $messageId, [
    'inline_keyboard' => [
        [['text' => '新按钮', 'callback_data' => 'new_option']]
    ]
]);
```

### 删除消息

```php
$bot->deleteMessage($chatId, $messageId);
```

## 🛠️ 错误处理

```php
use XBot\Telegram\Exceptions\ApiException;
use XBot\Telegram\Exceptions\HttpException;

try {
    $message = $bot->sendMessage($chatId, 'Hello!');
} catch (ApiException $e) {
    // Telegram API 错误
    echo "API 错误: " . $e->getDescription();
    echo "错误代码: " . $e->getErrorCode();
} catch (HttpException $e) {
    // 网络错误
    echo "网络错误: " . $e->getMessage();
}
```

## 💡 最佳实践

### 1. 消息长度限制

```php
$longText = "很长的文本...";

// 检查文本长度
if (strlen($longText) > 4096) {
    // 分割长消息
    $chunks = str_split($longText, 4000);
    foreach ($chunks as $chunk) {
        $bot->sendMessage($chatId, $chunk);
        usleep(100000); // 避免速率限制
    }
} else {
    $bot->sendMessage($chatId, $longText);
}
```

### 2. 批量操作

```php
$userIds = [123456789, 987654321, 555666777];
$message = '重要通知：系统将于今晚维护！';

foreach ($userIds as $userId) {
    try {
        $bot->sendMessage($userId, $message);
        echo "✅ 消息已发送到: $userId\n";
        
        // 避免触发速率限制
        usleep(100000); // 0.1 秒延迟
    } catch (Exception $e) {
        echo "❌ 发送失败到 $userId: " . $e->getMessage() . "\n";
    }
}
```

### 3. 文件大小检查

```php
$filePath = '/path/to/large-file.pdf';
$fileSize = filesize($filePath);

// Telegram 文件大小限制为 50MB
if ($fileSize <= 50 * 1024 * 1024) {
    $bot->sendDocument($chatId, fopen($filePath, 'r'));
} else {
    $bot->sendMessage($chatId, '文件太大，无法发送');
}
```

## 🔗 相关链接

- [快速开始](../guide/quick-start.md)
- [API 参考](../api/)
- [Laravel 集成](laravel-integration.md)
- [高级特性](advanced-features.md)