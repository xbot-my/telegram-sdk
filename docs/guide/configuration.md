# ⚙️ 配置说明

本文档详细介绍 Telegram Bot PHP SDK 的所有配置选项和最佳实践。

## 📋 配置概览

SDK 支持多种配置方式：

- **直接配置** - 通过构造函数参数
- **数组配置** - 使用配置数组
- **Laravel 配置** - 通过 Laravel 配置文件
- **环境变量** - 使用 `.env` 文件

### 使用 `Bot::init` 的数组结构（推荐）

```php
use XBot\\Telegram\\Bot;

Bot::init([
  'default' => 'main',
  'bots' => [
    'main' => [
      'token' => 'YOUR_BOT_TOKEN',
      'base_url' => 'https://api.telegram.org/bot',
      'timeout' => 30,
      'retry_attempts' => 3,
      'retry_delay' => 1000,
      // 可选：webhook_url、webhook_secret 等
    ],
    // 'marketing' => ['token' => '...'],
  ],
]);
```

## 🏗️ 基础配置

### 1. HTTP 客户端配置

HTTP 客户端是与 Telegram API 通信的核心组件：

```php
use XBot\Telegram\Http\GuzzleHttpClient;

$httpClient = new GuzzleHttpClient($token, [
    // 基础配置
    'timeout' => 30,                // 请求超时（秒）
    'connect_timeout' => 10,        // 连接超时（秒）
    'debug' => false,               // 调试模式
    
    // 重试配置
    'retries' => 3,                 // 重试次数
    'retry_delay' => 1,             // 重试延迟（秒）
    'retry_multiplier' => 2,        // 重试延迟倍数
    
    // 代理配置
    'proxy' => [
        'http' => 'http://proxy.example.com:8080',
        'https' => 'https://proxy.example.com:8080',
    ],
    
    // SSL 配置
    'verify' => true,               // 验证 SSL 证书
    'cert' => '/path/to/cert.pem',  // 客户端证书
    
    // 请求头配置
    'headers' => [
        'User-Agent' => 'MyBot/1.0',
        'Accept' => 'application/json',
    ],
]);
```

### 2. Bot 实例配置

创建 Bot 实例时的配置选项：

```php
use XBot\Telegram\BotManager;

$manager = new BotManager();

$bot = $manager->createBot('my-bot', $httpClient, [
    // 基础设置
    'name' => 'my-bot',             // Bot 名称
    'description' => 'My Awesome Bot', // Bot 描述
    
    // 缓存配置
    'cache' => [
        'enabled' => true,          // 启用缓存
        'ttl' => 3600,             // 缓存时间（秒）
        'prefix' => 'telegram:',   // 缓存前缀
    ],
    
    // 日志配置
    'logging' => [
        'enabled' => true,          // 启用日志
        'level' => 'info',         // 日志级别
        'channel' => 'telegram',   // 日志通道
    ],
    
    // 异常处理
    'exceptions' => [
        'auto_retry' => true,       // 自动重试
        'max_retries' => 3,        // 最大重试次数
        'backoff' => 'exponential', // 退避策略
    ],
    
    // Webhook 配置
    'webhook' => [
        'url' => 'https://example.com/webhook',
        'certificate' => null,     // 自签名证书
        'max_connections' => 40,   // 最大连接数
        'allowed_updates' => [],   // 允许的更新类型
    ],
]);
```

## 🎛️ Laravel 配置

### 配置文件结构

Laravel 配置文件 `config/telegram.php` 的完整结构：

```php
<?php

return [
    /*
    |--------------------------------------------------------------------------
    | 默认 Bot
    |--------------------------------------------------------------------------
    |
    | 指定默认使用的 Bot 名称，当没有明确指定 Bot 时使用
    |
    */
    'default' => env('TELEGRAM_DEFAULT_BOT', 'main'),

    /*
    |--------------------------------------------------------------------------
    | Bot 配置
    |--------------------------------------------------------------------------
    |
    | 配置多个 Bot 实例，每个 Bot 都有独立的配置
    |
    */
    'bots' => [
        // 主 Bot 配置
        'main' => [
            'token' => env('TELEGRAM_MAIN_BOT_TOKEN'),
            'description' => '主要业务 Bot',
            
            'webhook' => [
                'url' => env('TELEGRAM_MAIN_WEBHOOK_URL'),
                'certificate' => env('TELEGRAM_MAIN_WEBHOOK_CERT'),
                'max_connections' => 40,
                'allowed_updates' => [
                    'message',
                    'callback_query',
                    'inline_query',
                ],
            ],
            
            'http_client' => [
                'timeout' => 30,
                'connect_timeout' => 10,
                'retries' => 3,
                'debug' => env('APP_DEBUG', false),
            ],
            
            'cache' => [
                'enabled' => true,
                'ttl' => 3600,
                'store' => env('CACHE_DRIVER', 'file'),
            ],
            
            'rate_limit' => [
                'enabled' => true,
                'max_attempts' => 30,
                'decay_minutes' => 1,
            ],
        ],
        
        // 客服 Bot 配置
        'customer-service' => [
            'token' => env('TELEGRAM_CS_BOT_TOKEN'),
            'description' => '客服 Bot',
            
            'webhook' => [
                'url' => env('TELEGRAM_CS_WEBHOOK_URL'),
                'max_connections' => 20,
                'allowed_updates' => ['message'],
            ],
            
            'http_client' => [
                'timeout' => 15,
                'retries' => 2,
            ],
            
            'rate_limit' => [
                'max_attempts' => 60,
                'decay_minutes' => 1,
            ],
        ],
        
        // 通知 Bot 配置
        'notifications' => [
            'token' => env('TELEGRAM_NOTIFY_BOT_TOKEN'),
            'description' => '系统通知 Bot',
            
            'http_client' => [
                'timeout' => 60,
                'retries' => 5,
            ],
            
            'cache' => [
                'enabled' => false, // 通知不需要缓存
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | 全局配置
    |--------------------------------------------------------------------------
    |
    | 适用于所有 Bot 的全局设置
    |
    */
    'global' => [
        // 异步处理
        'async' => env('TELEGRAM_ASYNC', false),
        
        // 队列配置
        'queue' => [
            'enabled' => env('TELEGRAM_QUEUE_ENABLED', false),
            'connection' => env('TELEGRAM_QUEUE_CONNECTION', 'default'),
            'queue' => env('TELEGRAM_QUEUE_NAME', 'telegram'),
        ],
        
        // 缓存配置
        'cache' => [
            'enabled' => env('TELEGRAM_CACHE_ENABLED', true),
            'store' => env('TELEGRAM_CACHE_STORE', 'default'),
            'ttl' => env('TELEGRAM_CACHE_TTL', 3600),
            'prefix' => env('TELEGRAM_CACHE_PREFIX', 'telegram:'),
        ],
        
        // 日志配置
        'logging' => [
            'enabled' => env('TELEGRAM_LOGGING_ENABLED', true),
            'channel' => env('TELEGRAM_LOG_CHANNEL', 'default'),
            'level' => env('TELEGRAM_LOG_LEVEL', 'info'),
        ],
        
        // 速率限制
        'rate_limit' => [
            'enabled' => env('TELEGRAM_RATE_LIMIT_ENABLED', true),
            'max_attempts' => env('TELEGRAM_RATE_LIMIT_MAX_ATTEMPTS', 30),
            'decay_minutes' => env('TELEGRAM_RATE_LIMIT_DECAY_MINUTES', 1),
            'store' => env('TELEGRAM_RATE_LIMIT_STORE', 'default'),
        ],
        
        // 安全配置
        'security' => [
            'verify_webhook' => env('TELEGRAM_VERIFY_WEBHOOK', true),
            'webhook_secret' => env('TELEGRAM_WEBHOOK_SECRET'),
            'allowed_ips' => array_filter(explode(',', env('TELEGRAM_ALLOWED_IPS', ''))),
        ],
        
        // 错误处理
        'error_handling' => [
            'auto_retry' => env('TELEGRAM_AUTO_RETRY', true),
            'max_retries' => env('TELEGRAM_MAX_RETRIES', 3),
            'backoff_strategy' => env('TELEGRAM_BACKOFF_STRATEGY', 'exponential'),
            'report_exceptions' => env('TELEGRAM_REPORT_EXCEPTIONS', true),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | 中间件配置
    |--------------------------------------------------------------------------
    |
    | Webhook 处理中间件的配置
    |
    */
    'middleware' => [
        'webhook' => [
            'verify_signature' => env('TELEGRAM_VERIFY_SIGNATURE', true),
            'rate_limit' => env('TELEGRAM_WEBHOOK_RATE_LIMIT', true),
            'log_requests' => env('TELEGRAM_LOG_WEBHOOK_REQUESTS', true),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | 命令配置
    |--------------------------------------------------------------------------
    |
    | Artisan 命令的配置
    |
    */
    'commands' => [
        'info' => [
            'show_token' => env('TELEGRAM_SHOW_TOKEN_IN_INFO', false),
        ],
        'health_check' => [
            'timeout' => env('TELEGRAM_HEALTH_CHECK_TIMEOUT', 10),
        ],
    ],
];
```

### 环境变量配置

在 `.env` 文件中添加相应的环境变量：

```env
# 默认 Bot
TELEGRAM_DEFAULT_BOT=main

# 主 Bot 配置
TELEGRAM_MAIN_BOT_TOKEN=123456789:AABBCCDDEEFFGGHHIIJJKKLLMMNNOOPPQQRRa
TELEGRAM_MAIN_WEBHOOK_URL=https://yourapp.com/telegram/webhook/main
TELEGRAM_MAIN_WEBHOOK_CERT=

# 客服 Bot 配置
TELEGRAM_CS_BOT_TOKEN=987654321:XYZabc123DEFghi456JKLmnop789QRSTuvw
TELEGRAM_CS_WEBHOOK_URL=https://yourapp.com/telegram/webhook/customer-service

# 通知 Bot 配置
TELEGRAM_NOTIFY_BOT_TOKEN=555666777:NotifyTokenHereAbcDef123GhiJkl456Mno

# 全局配置
TELEGRAM_ASYNC=false
TELEGRAM_CACHE_ENABLED=true
TELEGRAM_CACHE_TTL=3600
TELEGRAM_RATE_LIMIT_ENABLED=true
TELEGRAM_RATE_LIMIT_MAX_ATTEMPTS=30

# 安全配置
TELEGRAM_VERIFY_WEBHOOK=true
TELEGRAM_WEBHOOK_SECRET=your-secret-key
TELEGRAM_ALLOWED_IPS=149.154.160.0/20,91.108.4.0/22

# 队列配置（可选）
TELEGRAM_QUEUE_ENABLED=false
TELEGRAM_QUEUE_CONNECTION=redis
TELEGRAM_QUEUE_NAME=telegram-bot

# 日志配置
TELEGRAM_LOGGING_ENABLED=true
TELEGRAM_LOG_CHANNEL=telegram
TELEGRAM_LOG_LEVEL=info
```

## 🔧 高级配置

### 1. 自定义 HTTP 客户端

如果需要使用自定义的 HTTP 客户端：

```php
use XBot\Telegram\Contracts\HttpClientInterface;
use XBot\Telegram\Models\Response\TelegramResponse;

class CustomHttpClient implements HttpClientInterface
{
    private string $token;
    private array $config;
    
    public function __construct(string $token, array $config = [])
    {
        $this->token = $token;
        $this->config = $config;
    }
    
    public function post(string $method, array $parameters = []): TelegramResponse
    {
        // 自定义 HTTP 请求实现
        $url = "https://api.telegram.org/bot{$this->token}/{$method}";
        
        // 执行请求...
        $response = $this->makeRequest($url, $parameters);
        
        return new TelegramResponse($response);
    }
    
    public function upload(string $method, array $parameters = [], array $files = []): TelegramResponse
    {
        // 自定义文件上传实现
        // ...
    }
    
    public function getToken(): string
    {
        return $this->token;
    }
    
    private function makeRequest(string $url, array $data): array
    {
        // 实现具体的 HTTP 请求逻辑
        // ...
    }
}

// 使用自定义客户端
$httpClient = new CustomHttpClient($token, $customConfig);
$bot = $manager->createBot('custom', $httpClient);
```

### 2. 缓存策略配置

配置不同的缓存策略：

```php
// Redis 缓存配置
$config = [
    'cache' => [
        'enabled' => true,
        'driver' => 'redis',
        'connection' => 'default',
        'ttl' => 3600,
        'prefix' => 'telegram:bot:',
        'tags' => ['telegram', 'bot'],
    ],
];

// Memcached 缓存配置
$config = [
    'cache' => [
        'enabled' => true,
        'driver' => 'memcached',
        'ttl' => 1800,
        'prefix' => 'tg_',
    ],
];

// 文件缓存配置
$config = [
    'cache' => [
        'enabled' => true,
        'driver' => 'file',
        'path' => '/tmp/telegram-cache',
        'ttl' => 7200,
    ],
];
```

### 3. 队列配置

配置异步队列处理：

```php
// config/telegram.php
'queue' => [
    'enabled' => true,
    'connection' => 'redis',
    'queue' => 'telegram-high',
    'delay' => 0,
    'timeout' => 60,
    'tries' => 3,
    'backoff' => [10, 30, 60], // 重试间隔（秒）
],
```

创建队列任务：

```php
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use XBot\Telegram\Facades\Telegram;

class SendTelegramMessage implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;
    
    public function __construct(
        private string $botName,
        private int|string $chatId,
        private string $text,
        private array $options = []
    ) {}
    
    public function handle(): void
    {
        Telegram::bot($this->botName)->sendMessage(
            $this->chatId,
            $this->text,
            $this->options
        );
    }
}

// 分发队列任务
SendTelegramMessage::dispatch('main', 123456789, 'Hello from queue!');
```

### 4. 中间件配置

配置 Webhook 处理中间件：

```php
// app/Http/Middleware/TelegramWebhookAuth.php
class TelegramWebhookAuth
{
    public function handle(Request $request, Closure $next)
    {
        $signature = $request->header('X-Telegram-Bot-Api-Secret-Token');
        $expectedSignature = config('telegram.global.security.webhook_secret');
        
        if (!hash_equals($expectedSignature, $signature)) {
            abort(403, 'Unauthorized');
        }
        
        return $next($request);
    }
}

// 在路由中使用
Route::post('/telegram/webhook/{bot}', [WebhookController::class, 'handle'])
    ->middleware(['telegram.webhook.auth', 'telegram.rate.limit']);
```

## 🔍 配置验证

### 验证配置正确性

创建配置验证脚本：

```php
<?php

require_once 'vendor/autoload.php';

use XBot\Telegram\BotManager;
use XBot\Telegram\Http\GuzzleHttpClient;

function validateConfig(array $config): array
{
    $errors = [];
    
    // 验证必需配置
    if (empty($config['token'])) {
        $errors[] = 'Bot token 不能为空';
    }
    
    if (!preg_match('/^\d+:[A-Za-z0-9_-]+$/', $config['token'] ?? '')) {
        $errors[] = 'Bot token 格式无效';
    }
    
    // 验证超时配置
    if (isset($config['http_client']['timeout']) && $config['http_client']['timeout'] < 1) {
        $errors[] = 'HTTP 超时时间必须大于 0';
    }
    
    // 验证重试配置
    if (isset($config['http_client']['retries']) && $config['http_client']['retries'] < 0) {
        $errors[] = '重试次数不能为负数';
    }
    
    return $errors;
}

// 验证配置
$config = [
    'token' => 'YOUR_BOT_TOKEN',
    'http_client' => [
        'timeout' => 30,
        'retries' => 3,
    ],
];

$errors = validateConfig($config);

if (empty($errors)) {
    echo "✅ 配置验证通过\n";
    
    // 测试连接
    try {
        $httpClient = new GuzzleHttpClient($config['token']);
        $manager = new BotManager();
        $bot = $manager->createBot('test', $httpClient);
        
        $botInfo = $bot->getMe();
        echo "🤖 Bot 连接成功: @{$botInfo->username}\n";
    } catch (Exception $e) {
        echo "❌ 连接测试失败: {$e->getMessage()}\n";
    }
} else {
    echo "❌ 配置验证失败:\n";
    foreach ($errors as $error) {
        echo "  - $error\n";
    }
}
```

## 🔒 安全配置

### Webhook 安全配置

```php
// 配置 Webhook 安全选项
'webhook' => [
    'url' => 'https://yourapp.com/telegram/webhook',
    'certificate' => '/path/to/webhook_cert.pem', // 自签名证书
    'secret_token' => env('TELEGRAM_WEBHOOK_SECRET'), // 密钥令牌
    'max_connections' => 40,
    'allowed_updates' => [
        'message',
        'callback_query',
        // 只接收需要的更新类型
    ],
],

// IP 白名单
'security' => [
    'allowed_ips' => [
        '149.154.160.0/20',  // Telegram 官方 IP 范围
        '91.108.4.0/22',
        '91.108.56.0/22',
        '91.108.8.0/22',
        '149.154.164.0/22',
        '149.154.168.0/22',
        '149.154.172.0/22',
    ],
    'verify_ssl' => true,
    'webhook_secret' => env('TELEGRAM_WEBHOOK_SECRET'),
],
```

## 📊 性能优化配置

### 连接池配置

```php
'http_client' => [
    'timeout' => 30,
    'connect_timeout' => 5,
    'pool_size' => 10,          // 连接池大小
    'keep_alive' => true,       // 保持连接
    'max_retries' => 3,
    'retry_delay' => 1000,      // 毫秒
    'compression' => 'gzip',    // 压缩
],
```

### 缓存优化

```php
'cache' => [
    'enabled' => true,
    'driver' => 'redis',
    'ttl' => 3600,
    'tags_enabled' => true,
    'compression' => true,
    'serializer' => 'igbinary', // 更快的序列化
],
```

## 🚀 生产环境配置

### 生产环境最佳实践

```env
# 生产环境配置
APP_ENV=production
APP_DEBUG=false

# Telegram 配置
TELEGRAM_ASYNC=true
TELEGRAM_QUEUE_ENABLED=true
TELEGRAM_QUEUE_CONNECTION=redis

# 缓存配置
TELEGRAM_CACHE_ENABLED=true
TELEGRAM_CACHE_STORE=redis
TELEGRAM_CACHE_TTL=7200

# 日志配置
TELEGRAM_LOGGING_ENABLED=true
TELEGRAM_LOG_LEVEL=warning

# 安全配置
TELEGRAM_VERIFY_WEBHOOK=true
TELEGRAM_WEBHOOK_SECRET=your-production-secret

# 速率限制
TELEGRAM_RATE_LIMIT_ENABLED=true
TELEGRAM_RATE_LIMIT_MAX_ATTEMPTS=100
```

## 🔧 故障排除

### 常见配置问题

1. **Token 格式错误**
   ```
   错误: Invalid token format
   解决: 确保 Token 格式为 "数字:字母数字字符串"
   ```

2. **连接超时**
   ```
   错误: Connection timeout
   解决: 增加 timeout 和 connect_timeout 值
   ```

3. **速率限制**
   ```
   错误: Too Many Requests
   解决: 调整 rate_limit 配置或增加延迟
   ```

## ➡️ 下一步

配置完成后，您可以：

1. 🚀 尝试 [使用示例](../examples/basic-usage.md)
2. 📖 查看 [API 参考](../api/)
3. ⭐ 学习 [最佳实践](../best-practices/)

---

💡 **提示**: 定期检查和更新配置，确保 Bot 的最佳性能和安全性。
