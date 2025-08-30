# Telegram Bot PHP SDK

一个功能强大、易于使用的 PHP Telegram Bot API SDK，支持多 Token、多 Bot、多实例管理，实例间完全隔离互不干扰。

## ✨ 特性

- 🤖 **多 Bot 支持** - 支持同时管理多个 Bot 实例
- 🔒 **实例隔离** - 每个 Bot 实例完全独立，互不干扰
- ⚡ **高性能** - 基于 Guzzle HTTP 客户端，支持连接池和异步请求
- 🎯 **语义端点** - 按 Telegram API 语义拆分 Endpoint（无繁重 DTO）
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

```php
use XBot\Telegram\Bot;           // Helper for quick client setup

$bot = Bot::token('YOUR_BOT_TOKEN'); // returns TelegramBot

// Endpoints return a Transformer for easy formatting
$me      = $bot->getMe()->toArray();
$meObj   = $bot->getMe()->toObject();
$meJson  = $bot->getMe()->toJson();

// Send and fetch
$msg  = $bot->sendMessage(123456789, 'Hello')->toArray();
$chat = $bot->getChat(123456789)->toArray();

// Webhook
$bot->setWebhook('https://example.com/telegram/webhook');
$info = $bot->getWebhookInfo()->toArray();
$bot->deleteWebhook(dropPendingUpdates: true);
```

## 🔔 Webhook 与更新处理

1) 配置与设置 Webhook
- 在环境变量中设置 `TELEGRAM_WEBHOOK_SECRET`。
- 可选：设置 `TELEGRAM_WEBHOOK_ROUTE_PREFIX`（默认 `telegram/webhook`）。
- 设置 Webhook（附带密钥）：

```php
$bot->setWebhook('https://yourapp.com/telegram/webhook', [
    'secret_token' => env('TELEGRAM_WEBHOOK_SECRET'),
]);
```

2) 默认路由与中间件
- ServiceProvider 会注册一个 POST 路由到 `telegram/webhook`，默认中间件为 `api`, `telegram.webhook`。
- 中间件会校验请求头 `X-Telegram-Bot-Api-Secret-Token` 与配置的密钥是否匹配。
- 默认会在容器中注册一个 `XBot\Telegram\TelegramBot` 单例，可在处理器中通过依赖注入获取。

3) 处理器（Handlers）与分发器（Dispatcher）
- 你可以实现接口 `XBot\\Telegram\\Contracts\\UpdateHandler`，或继承 `XBot\\Telegram\\Handlers\\BaseUpdateHandler`。
- 在 `config/telegram.php` 中注册处理器：

```php
'webhook' => [
    // ...
    'handlers' => [
        App\\Telegram\\Handlers\\StartHandler::class,
    ],
],
```

4) BaseUpdateHandler 路由辅助
- 继承 `BaseUpdateHandler` 并实现以下任意方法即可按更新类型自动路由：
  - `onMessage(array $u)`、`onEditedMessage(array $u)`、`onChannelPost(array $u)`、`onEditedChannelPost(array $u)`
  - `onInlineQuery(array $u)`、`onChosenInlineResult(array $u)`、`onCallbackQuery(array $u)`
  - `onShippingQuery(array $u)`、`onPreCheckoutQuery(array $u)`、`onPoll(array $u)`、`onPollAnswer(array $u)`
  - `onMyChatMember(array $u)`、`onChatMember(array $u)`、`onChatJoinRequest(array $u)`
  - 或实现 `onUpdate(array $u)` 作为兜底

示例：

```php
use XBot\\Telegram\\Handlers\\BaseUpdateHandler;

class StartHandler extends BaseUpdateHandler
{
    protected function onMessage(array $u): void
    {
        if ($this->text($u) === '/start') {
            $this->replyText($u, 'Welcome!');
        }
    }
}
```

5) Laravel 事件
- 每条更新会触发 `XBot\\Telegram\\Events\\TelegramUpdateReceived` 事件，可用于监听。

### 命令路由（/command）
- 继承 `XBot\\Telegram\\Handlers\\CommandRouter` 可自动将以 `/` 开头的文本路由到对应方法：
  - `/start` → `onStart(array $u)`
  - `/help foo bar` → `onHelp(array $u, string ...$args)`
  - 未定义命令 → `onCommand(array $u, string $command, array $args)`

示例：

```
use XBot\\Telegram\\Handlers\\CommandRouter;

class MyCommands extends CommandRouter
{
    protected function onStart(array $u): void { $this->replyText($u, 'Welcome!'); }
    protected function onHelp(array $u, string ...$args): void { $this->replyText($u, 'Help: ' . implode(' ', $args)); }
    protected function onCommand(array $u, string $cmd, array $args): void { $this->replyText($u, 'Unknown: ' . $cmd); }
}
```

在 `config/telegram.php` 中注册：

```
'webhook' => [
  // ...
  'handlers' => [ App\\Telegram\\Handlers\\MyCommands::class ],
],
```

提示：处理器会通过容器自动注入 `XBot\\Telegram\\Bot` 实例，可直接在命令方法中发送消息。

## 💼 WebApp / Business / Boosts / Stars 示例

- WebApp 结果应答：
```php
$bot->answerWebAppQuery($queryId, [
  'type' => 'article',
  'id' => '1',
  'title' => 'Result',
  'input_message_content' => ['message_text' => 'Hello from WebApp'],
]);
```

- Chat Boosts 查询：
```php
$bot->getUserChatBoosts($chatId, $userId)->toArray();
$bot->getChatBoosts($chatId)->toArray();
```

- Stars：
```php
$bot->refundStarPayment($userId, $chargeId);
$balance = $bot->getMyStarBalance()->toArray()['balance'] ?? 0;
```

- Business：
```php
$bot->readBusinessMessage($chatId, $messageId);
$bot->deleteBusinessMessages($chatId, [$messageId1, $messageId2]);
$bot->setBusinessAccountName('My Business');
$bot->approveSuggestedPost($chatId, $messageId);
```

更多端点与映射参见 `docs/API_COVERAGE.md`。

## 🆕 Bot API 9.2 选项透传说明
- SDK 端点均支持通过 `$options` 透传最新参数；数组会自动 JSON 化。
- 重要新增参数：
  - `direct_messages_topic_id`：可用于 sendMessage/sendPhoto/sendVideo/sendAnimation/sendAudio/sendDocument/sendPaidMedia/sendSticker/sendVideoNote/sendVoice/sendLocation/sendVenue/sendContact/sendDice/sendInvoice/copy/forward 等方法，将消息发送到频道私信话题。
  - `suggested_post_parameters`：用于上述发送类方法配合“建议帖子（Suggested Posts）”。
  - `reply_parameters.checklist_task_id`：回复到特定清单任务（Checklists）。

示例：
```php
// 发送到频道私信话题
$bot->sendMessage($chatId, 'Hello topic', [
  'direct_messages_topic_id' => 1234,
]);

// 建议帖子（需管理员审批）
$bot->sendPhoto($chatId, 'file_id_or_path', [
  'caption' => 'Hi',
  'suggested_post_parameters' => [
    // 价格、受众等参数按官方文档填充
  ],
]);

// 回复到清单任务
$bot->sendMessage($chatId, 'Task response', [
  'reply_parameters' => [ 'checklist_task_id' => 999 ],
]);
```

## 📖 说明
- SDK 不负责持久化；需要在你的应用层处理。
- 若需集合返回格式，安装 `illuminate/support` 后使用 `->collection()`。

## 🪵 日志配置
- 通过 `.env` 控制日志：
  - `TELEGRAM_LOG_ENABLED=true|false` 开启/关闭日志
  - `TELEGRAM_LOG_SUPPRESS_INFO=true|false` 仅保留告警/错误，屏蔽 info（请求/响应）
  - `TELEGRAM_LOG_CHANNEL=stack` 指定 Laravel 日志渠道
- 记录事件：
  - `telegram.request`、`telegram.response{ elapsed_ms }`
  - 失败：`telegram.client_exception`、`server_exception`、`connect_exception`、`redirect_exception`、`request_exception`、`unexpected_exception`
  - 重试：`telegram.retry{ attempt, reason }`、`telegram.retry.delay{ attempt, delay_ms }`

## 🧭 设计理念
- 简单：优先返回原始结构，通过 Transformer 决定输出形态。
- 可选：按需调用 `->toArray() | ->toObject() | ->toJson()`。
