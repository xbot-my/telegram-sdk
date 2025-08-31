# Laravel 集成

#### XBot Telegram SDK 为 Laravel 应用提供了开箱即用的集成支持，包含 ServiceProvider、配置文件、路由和中间件。

---

## 安装与发布配置

* composer 安装命令.

```bash
composer require xbot-my/telegram-sdk
````

* 发布配置文件：

```bash
php artisan vendor:publish --provider="XBot\Telegram\Providers\TelegramServiceProvider"
```

上述命令会在 `config/telegram.php` 中生成默认配置，并注册 ServiceProvider。这段指南来自项目文档中的安装部分【736136284671642†L17-L33】。

## Webhook 路由与中间件

> ServiceProvider 会自动注册一个 POST 路由，
> 例如 `telegram/webhook`，并应用 `api` 与 `telegram.webhook` 中间件。
> 中间件会校验请求头 `X-Telegram-Bot-Api-Secret-Token` 是否与环境变量中的 `TELEGRAM_WEBHOOK_SECRET` 一致。
> 你可以通过以下步骤启用 Webhook：

* `.env` 文件中设置：

```dotenv
# webhook 密钥
TELEGRAM_WEBHOOK_SECRET=your-secret-token
# 自定义路由前缀
TELEGRAM_WEBHOOK_ROUTE_PREFIX=telegram/webhook
```

* 使用 Bot 实例设置 Webhook：

 ```php
   $bot->setWebhook('https://yourapp.com/telegram/webhook', [
       'secret_token' => config('telegram.webhook.secret_token'),
   ]);
```

通过上述配置，Telegram 将把收到的更新推送到你的应用，SDK 会自动解析并分发更新对象。

## Update 处理器与分发器

SDK 使用“处理器 (Handler)”模式处理更新。你可以实现接口 `XBot\Telegram\Contracts\UpdateHandler`，或继承 `XBot\Telegram\Handlers\BaseUpdateHandler`，并在 `config/telegram.php` 中注册：

```php
'webhook' => [
    // ...
    'handlers' => [
        App\Telegram\Handlers\StartHandler::class,
    ],
],
```

BaseUpdateHandler 提供了一组以 `onXxx` 命名的方法，对应不同类型的更新，例如消息、编辑消息、回调查询等。SDK 会根据更新类型自动调用相应方法【736136284671642†L75-L99】。以下是一个简单示例：

```php
use XBot\Telegram\Handlers\BaseUpdateHandler;

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

除此之外，SDK 还会在每条更新到达时触发 `XBot\Telegram\Events\TelegramUpdateReceived` 事件，方便你在应用其他地方监听【736136284671642†L117-L119】。

## 命令路由

继承 `XBot\Telegram\Handlers\CommandRouter` 可以自动将以 `/` 开头的文本路由到对应方法，而无需手动匹配命令【736136284671642†L120-L149】。约定如下：

- `/start` 调用 `onStart(array $update)`
- `/help foo bar` 调用 `onHelp(array $update, string ...$args)`
- 未定义的命令调用 `onCommand(array $update, string $cmd, array $args)`

例如：

```php
use XBot\Telegram\Handlers\CommandRouter;

class MyCommands extends CommandRouter
{
    protected function onStart(array $u): void
    {
        $this->replyText($u, 'Welcome!');
    }
    protected function onHelp(array $u, string ...$args): void
    {
        $this->replyText($u, 'Help: ' . implode(' ', $args));
    }
    protected function onCommand(array $u, string $cmd, array $args): void
    {
        $this->replyText($u, 'Unknown: ' . $cmd);
    }
}
```

在配置文件中注册此处理器即可：

```php
'webhook' => [
    'handlers' => [
        App\Telegram\Handlers\MyCommands::class,
    ],
],
```

命令处理器会自动注入 `XBot\Telegram\Bot` 实例，因此你可以直接调用 `$this->bot()` 或 `$this->sendMessage()` 等方法发送回复。

## 小结

Laravel 集成旨在最大化框架生态的便利性。通过配置文件和自动注入，你几乎不需要关心底层网络或验证逻辑，只需聚焦业务处理即可。如果需要更细粒度的控制，可阅读源码并覆盖 ServiceProvider 的默认行为。
