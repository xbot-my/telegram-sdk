# 🤖 TelegramBot

`TelegramBot` 是 SDK 的核心类，代表一个独立的 Bot 实例，封装了 Telegram Bot API 的主要能力。通常通过入口 `Bot::bot()` 或 `BotManager` 获取实例。

## 快速上手

```php
use XBot\\Telegram\\Bot;

Bot::init(['default' => 'main', 'bots' => [
  'main' => ['token' => 'YOUR_BOT_TOKEN'],
]]);

$bot = Bot::bot();
$bot->sendMessage(123456789, 'Hello');
```

## 常用方法

- 发送消息: `sendMessage(chatId, text, options)`
- 编辑/删除: `editMessageText(chatId, messageId, text, options)`, `deleteMessage(chatId, messageId)`
- 转发/复制: `forwardMessage(chatId, fromChatId, messageId)`, `copyMessage(chatId, fromChatId, messageId)`
- 媒体发送: `sendPhoto|Video|Audio|Document|Voice|Animation(chatId, file, options)`
- 位置/联系人/投票: `sendLocation`, `sendContact`, `sendPoll`
- 更新/Webhook: `getUpdates`, `setWebhook`, `getWebhookInfo`, `deleteWebhook`
- 文件/头像: `getFile`, `getUserProfilePhotos`
- 聊天信息: `getChat`, `getChatMember`, `getChatMemberCount`, `getChatAdministrators`
- 聊天管理: `setChatPhoto`, `deleteChatPhoto`, `setChatTitle`, `setChatDescription`, `pinChatMessage`, `unpinChatMessage`, `unpinAllChatMessages`, `leaveChat`
- 命令: `setMyCommands`, `getMyCommands`, `deleteMyCommands`

更多方法细节与示例，请参见：

- 💬 消息方法: [methods/message.md](methods/message.md)
- 👥 聊天方法: [methods/chat.md](methods/chat.md)
- 🔄 更新与 Webhook: [methods/update.md](methods/update.md)

## 校验与错误

SDK 对关键参数执行严格校验，例如：

- `chatId` 支持整数 ID、负数群组 ID、或 `@username`
- `text` 长度限制 0–4096
- Webhook URL 必须是 HTTPS
- Token 格式需满足 `^\d{8,10}:[a-zA-Z0-9_-]{35}$`

抛出的异常类型包括：

- `ValidationException` 参数校验失败
- `ApiException` Telegram API 返回错误
- `HttpException` 网络请求失败
- `ConfigurationException` 配置错误

## 示例：键盘与链式发送

```php
use XBot\\Telegram\\Bot;

Bot::init(['default' => 'main', 'bots' => ['main' => ['token' => 'TOKEN']]]);

Bot::to(123456789)
  ->html()
  ->keyboard([
    [['text' => '按钮1', 'callback_data' => 'btn1']],
    [['text' => '按钮2', 'callback_data' => 'btn2']],
  ])
  ->message('<b>请选择</b>');
```

> 提示：推荐通过 `Bot` 入口与 `BotMessage` 链式构建常见消息与键盘，既简洁又不失灵活。
