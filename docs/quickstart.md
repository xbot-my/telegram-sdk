# 快速开始

本节将演示如何安装 XBot Telegram SDK 并发送第一条消息。全部示例均基于 PHP 8.1 及以上版本。

## 安装 SDK

使用 Composer 安装软件包：

```bash
composer require xbot-my/telegram-sdk
```

> 上述命令来源于项目的安装指南【736136284671642†L17-L23】。

## 发送第一条消息

SDK 提供了一个 `Bot::token()` 辅助方法，用于快速创建 Bot 实例。调用任意端点方法后，会返回包含转换器的响应对象，支持数组、对象和 JSON 三种形态。

```php
use XBot\Telegram\Bot; // 引入助手

$bot = Bot::token('YOUR_BOT_TOKEN'); // 创建实例

// 获取当前 Bot 信息（返回 Transformer 对象）
$me      = $bot->getMe()->toArray();
$meObj   = $bot->getMe()->toObject();
$meJson  = $bot->getMe()->toJson();

// 发送消息
$msg  = $bot->sendMessage(123456789, 'Hello')->toArray();

// 查询聊天信息
$chat = $bot->getChat(123456789)->toArray();

// 设置 Webhook（可用于接收更新）
$bot->setWebhook('https://example.com/telegram/webhook');
$info = $bot->getWebhookInfo()->toArray();
$bot->deleteWebhook(dropPendingUpdates: true);
```

上述代码节选自项目 README【736136284671642†L34-L54】。你可以按需封装这些调用，或通过依赖注入在框架中复用。

## 下一步

如果你正在使用 Laravel，那么请继续阅读 [Laravel 集成](laravel.md) 了解如何发布配置、注册路由和注入服务。否则，前往 [Webhook 与更新处理](webhook.md) 学习如何手动处理更新。有关端点的详细映射，请访问 [API 覆盖](../reference/api_coverage.md)。
