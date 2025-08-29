# Telegram Bot PHP SDK

一个功能强大、易于使用的 PHP Telegram Bot API SDK，支持多 Token、多 Bot、多实例管理，实例间完全隔离互不干扰。

## ✨ 特性

- 🤖 **多 Bot 支持** - 支持同时管理多个 Bot 实例
- 🔒 **实例隔离** - 每个 Bot 实例完全独立，互不干扰
- ⚡ **高性能** - 基于 Guzzle HTTP 客户端，支持连接池和异步请求
- 🎯 **类型安全** - 完整的 DTO 模型和类型提示
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
use XBot\Telegram\Bot;
use XBot\Telegram\Models\Response\ResponseFormat as F;

$sdk = Bot::token('YOUR_BOT_TOKEN');

$me = $sdk->getMe(); // array
$meObj  = $sdk->as(F::OBJECT)->getMe();
$meJson = $sdk->as(F::JSON)->getMe();

$sdk->sendMessage(123456789, "Hello");
$chat = $sdk->chat()->getChat(123456789);

$sdk->setWebhook('https://example.com/telegram/webhook');
$info = $sdk->getWebhookInfo();
$sdk->deleteWebhook(dropPendingUpdates: true);
```

## 📖 说明
- SDK 不负责持久化；需要在你的应用层处理。
- 若需 `collection` 返回格式，安装 `illuminate/support` 后使用 `$sdk->as('collection')`。

## 🧭 设计理念
- 简单：尽量返回原始数组，不强行包装为 DTO。
- 可选：通过 `as()` 选择不同输出格式，满足不同场景。
