# Telegram Bot PHP SDK

一个易于使用的 PHP Telegram Bot API SDK，提供高性能客户端、语义化端点和完善的异常处理机制，并支持 Laravel 集成。

---

## 特性

* 基于 Guzzle 的高性能 HTTP 客户端
* 语义化端点，避免繁重 DTO
* 完整的异常与重试机制
* 请求统计与监控
* 开箱即用的 Laravel 集成
* 链式调用与统一响应转换

## 安装

```bash
composer require xbot-my/telegram-sdk
```

### Laravel 集成

```bash
php artisan vendor:publish --provider="XBot\\Telegram\\Providers\\TelegramServiceProvider"
```

## 快速开始

```php
use XBot\\Telegram\\Bot;

$bot = Bot::token('YOUR_BOT_TOKEN');

$me  = $bot->getMe()->toArray();
$msg = $bot->sendMessage(123456789, 'Hello')->toArray();
$bot->setWebhook('https://example.com/telegram/webhook');
```

## Webhook 与更新处理

* 在 `.env` 设置 `TELEGRAM_WEBHOOK_SECRET`，可选 `TELEGRAM_WEBHOOK_ROUTE_PREFIX`。
* ServiceProvider 注册默认路由与中间件，校验请求头 `X-Telegram-Bot-Api-Secret-Token`。
* 实现 `UpdateHandler` 或继承 `BaseUpdateHandler`：

```php
class StartHandler extends BaseUpdateHandler {
    protected function onMessage(array $u): void {
        if ($this->text($u) === '/start') $this->replyText($u, 'Welcome!');
    }
}
```

* 命令路由可继承 `CommandRouter`，如 `/start` → `onStart`，`/help foo` → `onHelp`。

## 示例

```php
// WebApp
$bot->answerWebAppQuery($queryId, [...]);
// Boosts
$bot->getUserChatBoosts($chatId, $userId);
// Stars
$bot->refundStarPayment($userId, $chargeId);
// Business
$bot->readBusinessMessage($chatId, $messageId);
```

## Bot API 9.2 新参数

* `direct_messages_topic_id`：发送至频道话题
* `suggested_post_parameters`：建议帖子
* `reply_parameters.checklist_task_id`：回复清单任务

```php
$bot->sendMessage($chatId, 'Hello', ['direct_messages_topic_id' => 1234]);
```

## 日志

* 环境变量控制：

    * `TELEGRAM_LOG_ENABLED`
    * `TELEGRAM_LOG_SUPPRESS_INFO`
    * `TELEGRAM_LOG_CHANNEL`
* 事件：`telegram.request`、`telegram.response`、`telegram.retry` 等
