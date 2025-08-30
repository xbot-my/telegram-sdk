---
title: Webhook 与更新处理
---

# Webhook 与更新处理

Telegram Bot API 支持两种更新获取方式：轮询 (`getUpdates`) 和 Webhook 推送。XBot Telegram SDK 推荐使用 Webhook，因为它能减少延迟并节省资源。本节介绍如何配置 Webhook、编写更新处理器以及如何利用命令路由。

## 配置 Webhook

要启用 Webhook，需要先在 Telegram 服务器上设置回调地址，并提供一个密钥用于验证来源。SDK 的 `setWebhook()` 方法接受回调 URL 和可选参数：

```php
$bot->setWebhook('https://yourapp.com/telegram/webhook', [
    'secret_token' => env('TELEGRAM_WEBHOOK_SECRET'),
]);
```

对应的密钥需要在你的应用环境中设置：

```ini
TELEGRAM_WEBHOOK_SECRET=your-secret-token
```

当 Telegram 推送更新时，中间件 `telegram.webhook` 会校验请求头 `X-Telegram-Bot-Api-Secret-Token` 是否与密钥一致【736136284671642†L56-L73】。

## 更新处理器（Handler）

更新是一种关联数组结构，包含消息、回调查询、内联查询等不同字段。为了方便开发，SDK 提供了处理器接口：

* 实现 `XBot\Telegram\Contracts\UpdateHandler` 以获得完整控制权。
* 继承 `XBot\Telegram\Handlers\BaseUpdateHandler` 以按更新类型自动路由。

BaseUpdateHandler 会根据更新中的键名调用对应方法，例如：

| 更新类型 | 方法 | 描述 |
|---------|------|------|
| 消息 | `onMessage(array $update)` | 新消息 |
| 编辑消息 | `onEditedMessage(array $update)` | 编辑后的消息 |
| 回调查询 | `onCallbackQuery(array $update)` | 点击按钮回调 |
| 内联查询 | `onInlineQuery(array $update)` | 用户向机器人发起内联查询 |

你也可以覆写 `onUpdate(array $update)` 作为兜底处理【736136284671642†L75-L100】。以下是一个简单的 Welcome 处理器：

```php
use XBot\Telegram\Handlers\BaseUpdateHandler;

class WelcomeHandler extends BaseUpdateHandler
{
    protected function onMessage(array $u): void
    {
        if ($this->text($u) === '/start') {
            $this->replyText($u, 'Hello, world!');
        }
    }
}
```

在 Laravel 中，处理器通过配置文件注册；在非 Laravel 项目中，你可以在接收更新的控制器中实例化处理器并调用其 `handle(array $update)` 方法。

## 命令路由

当更新是一条命令（以 `/` 开头）时，你可以继承 `XBot\Telegram\Handlers\CommandRouter`，让 SDK 自动根据命令名称路由到对应方法【736136284671642†L120-L149】。

```php
use XBot\Telegram\Handlers\CommandRouter;

class MyCommands extends CommandRouter
{
    protected function onStart(array $u): void { $this->replyText($u, 'Hi'); }
    protected function onHelp(array $u, string ...$args): void { $this->replyText($u, 'Help'); }
    protected function onCommand(array $u, string $cmd, array $args): void { $this->replyText($u, 'Unknown command'); }
}
```

注册该处理器后，SDK 会自动解析命令文本并注入 `XBot\Telegram\Bot` 实例。命令参数会作为可变参数传递给方法，如 `/help foo bar` 会被解析为 `$args = ['foo','bar']`。

## 事件监听

当更新到来时，SDK 会派发 `XBot\Telegram\Events\TelegramUpdateReceived` 事件。你可以在应用中监听此事件，实现日志记录、监控或消息过滤等逻辑【736136284671642†L117-L119】。

```php
Event::listen(\XBot\Telegram\Events\TelegramUpdateReceived::class, function ($event) {
    // $event->update 包含 Telegram 更新内容
    // 在这里记录日志或执行其他任务
});
```

## 小结

Webhook 方式推送实时高效，结合更新处理器和命令路由，能够让你的机器人快速响应用户请求。配置和注册处理器完成后，大部分工作就集中在实现业务逻辑上。不要忘记在生产环境中校验请求来源、处理异常，并在需要时使用重试策略。