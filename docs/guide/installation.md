# 📦 安装指南

本指南将详细说明如何安装和配置 Telegram Bot PHP SDK。

## 🔧 环境要求

在安装之前，请确保您的环境满足以下要求：

### 必需条件

- **PHP 版本**: >= 8.3.0
- **扩展**: 
  - `json` - JSON 处理
  - `curl` - HTTP 请求
  - `mbstring` - 多字节字符串处理
  - `openssl` - SSL/TLS 支持
- **Composer**: 最新版本

### 推荐条件

- **PHP 扩展**:
  - `redis` - Redis 缓存支持（可选）
  - `memcached` - Memcached 缓存支持（可选）
  - `sodium` - 高级加密支持（可选）

### Laravel 项目额外要求

- **Laravel 版本**: >= 10.0
- **PHP 版本**: >= 8.1.0

## 📥 安装方式

### 方式 1: 使用 Composer（推荐）

这是最简单和推荐的安装方式：

```bash
composer require xbot-my/telegram-sdk
```

### 方式 2: 指定版本安装

如果您需要安装特定版本：

```bash
# 安装最新稳定版
composer require xbot-my/telegram-sdk:^1.0

# 安装开发版本（不推荐用于生产环境）
composer require xbot-my/telegram-sdk:dev-main
```

### 方式 3: 从源码安装

适用于开发者或需要自定义的场景：

```bash
# 克隆仓库
git clone https://github.com/xbot-my/telegram-sdk.git

# 进入目录
cd telegram-sdk

# 安装依赖
composer install
```

## 🔍 验证安装

安装完成后，可以通过以下方式验证：

### 1. 检查类是否可用

```php
<?php

require_once 'vendor/autoload.php';

use XBot\Telegram\BotManager;
use XBot\Telegram\TelegramBot;

// 如果没有报错，说明安装成功
if (class_exists(BotManager::class)) {
    echo "✅ Telegram SDK 安装成功！\n";
} else {
    echo "❌ 安装失败，请检查 Composer 安装\n";
}
```

### 2. 创建简单的 Bot 实例

```php
<?php

require_once 'vendor/autoload.php';

use XBot\Telegram\BotManager;
use XBot\Telegram\Http\GuzzleHttpClient;

try {
    // 使用测试 Token（不会实际发送请求）
    $httpClient = new GuzzleHttpClient('123456789:TEST_TOKEN');
    $manager = new BotManager();
    $bot = $manager->createBot('test', $httpClient);
    
    echo "✅ Bot 实例创建成功！\n";
    echo "Bot 名称: " . $bot->getName() . "\n";
} catch (Exception $e) {
    echo "❌ Bot 创建失败: " . $e->getMessage() . "\n";
}
```

## 🏗️ Laravel 项目安装

如果您在 Laravel 项目中使用，需要执行额外的配置步骤：

### 1. 发布配置文件

```bash
php artisan vendor:publish --provider="XBot\Telegram\Providers\TelegramServiceProvider"
```

这将创建配置文件 `config/telegram.php`。

### 2. 配置环境变量

在 `.env` 文件中添加您的 Bot Token：

```env
# 主 Bot 配置
TELEGRAM_MAIN_BOT_TOKEN=123456789:AABBCCDDEEFFGGHHIIJJKKLLMMNNOOPPQQRRa
TELEGRAM_MAIN_WEBHOOK_URL=https://yourapp.com/telegram/webhook/main

# 可选：其他 Bot 配置
TELEGRAM_CUSTOMER_SERVICE_BOT_TOKEN=987654321:XYZabc123DEFghi456JKLmnop789QRSTuvw
TELEGRAM_CUSTOMER_SERVICE_WEBHOOK_URL=https://yourapp.com/telegram/webhook/customer-service
```

### 3. 配置文件说明

编辑 `config/telegram.php` 文件：

```php
<?php

return [
    /*
    |--------------------------------------------------------------------------
    | 默认 Bot
    |--------------------------------------------------------------------------
    |
    | 指定默认使用的 Bot 名称
    |
    */
    'default' => env('TELEGRAM_DEFAULT_BOT', 'main'),

    /*
    |--------------------------------------------------------------------------
    | Bot 配置
    |--------------------------------------------------------------------------
    |
    | 配置多个 Bot 实例
    |
    */
    'bots' => [
        'main' => [
            'token' => env('TELEGRAM_MAIN_BOT_TOKEN'),
            'webhook' => [
                'url' => env('TELEGRAM_MAIN_WEBHOOK_URL'),
                'certificate' => env('TELEGRAM_MAIN_WEBHOOK_CERT'),
                'max_connections' => 40,
                'allowed_updates' => ['message', 'callback_query'],
            ],
            'http_client' => [
                'timeout' => 30,
                'connect_timeout' => 10,
                'retries' => 3,
            ],
        ],
        
        'customer-service' => [
            'token' => env('TELEGRAM_CUSTOMER_SERVICE_BOT_TOKEN'),
            'webhook' => [
                'url' => env('TELEGRAM_CUSTOMER_SERVICE_WEBHOOK_URL'),
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | 全局配置
    |--------------------------------------------------------------------------
    */
    'global' => [
        'async' => false,
        'cache' => [
            'enabled' => true,
            'ttl' => 3600,
        ],
        'rate_limit' => [
            'enabled' => true,
            'max_attempts' => 30,
            'decay_minutes' => 1,
        ],
    ],
];
```

### 4. 注册服务提供者（Laravel 10 以下）

如果您使用的是 Laravel 10 以下版本，需要手动注册服务提供者。

在 `config/app.php` 的 `providers` 数组中添加：

```php
'providers' => [
    // 其他服务提供者...
    XBot\Telegram\Providers\TelegramServiceProvider::class,
],
```

在 `aliases` 数组中添加：

```php
'aliases' => [
    // 其他别名...
    'Telegram' => XBot\Telegram\Facades\Telegram::class,
],
```

### 5. 验证 Laravel 安装

运行以下 Artisan 命令验证安装：

```bash
# 检查配置
php artisan telegram:info

# 测试连接
php artisan telegram:health-check

# 查看统计信息
php artisan telegram:stats
```

## 🔐 获取 Bot Token

要使用 Telegram Bot API，您需要先创建一个 Bot 并获取 Token：

### 1. 与 BotFather 对话

1. 在 Telegram 中搜索 `@BotFather`
2. 发送 `/start` 开始对话
3. 发送 `/newbot` 创建新的 Bot

### 2. 配置 Bot 信息

1. 按提示输入 Bot 的显示名称
2. 输入 Bot 的用户名（必须以 `bot` 结尾）
3. BotFather 会返回您的 Bot Token

### 3. 保存 Token

将获得的 Token 保存在安全的地方，格式类似：
```
123456789:AABBCCDDEEFFGGHHIIJJKKLLMMNNOOPPQQRRa
```

> ⚠️ **安全提示**: 
> - 永远不要在代码中硬编码 Token
> - 不要将 Token 提交到版本控制系统
> - 使用环境变量来存储敏感信息

## 🐛 常见安装问题

### 问题 1: Composer 安装失败

**错误信息**:
```
Package xbot-my/telegram-sdk not found
```

**解决方案**:
```bash
# 更新 Composer
composer self-update

# 清除缓存
composer clear-cache

# 重新安装
composer require xbot-my/telegram-sdk
```

### 问题 2: PHP 版本不兼容

**错误信息**:
```
requires php >=8.3.0 but your php version is 8.2.x
```

**解决方案**:
1. 升级 PHP 到 8.3 或更高版本
2. 或者使用特定版本的 SDK（如果可用）

### 问题 3: 缺少必需的 PHP 扩展

**错误信息**:
```
Extension curl is missing from your system
```

**解决方案**:
```bash
# Ubuntu/Debian
sudo apt-get install php8.3-curl php8.3-json php8.3-mbstring

# CentOS/RHEL
sudo yum install php83-curl php83-json php83-mbstring

# macOS (Homebrew)
brew install php@8.3
```

### 问题 4: Laravel 自动发现失败

**解决方案**:
```bash
# 清除配置缓存
php artisan config:clear

# 清除自动加载缓存
composer dump-autoload

# 重新发布配置
php artisan vendor:publish --provider="XBot\Telegram\Providers\TelegramServiceProvider" --force
```

## 📞 获取帮助

如果您在安装过程中遇到问题：

1. 📖 查看 [常见问题](../troubleshooting/common-issues.md)
2. 🐛 在 GitHub 上 [提交 Issue](https://github.com/xbot-my/telegram-sdk/issues)
3. 💬 参与 [讨论区](https://github.com/xbot-my/telegram-sdk/discussions)

## ➡️ 下一步

安装完成后，您可以：

1. 🚀 阅读 [快速开始](quick-start.md) 指南
2. ⚙️ 了解 [配置说明](configuration.md)
3. 💡 查看 [使用示例](../examples/basic-usage.md)