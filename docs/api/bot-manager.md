# 🤖 BotManager API

`BotManager` 是 Telegram Bot PHP SDK 的核心管理类，负责创建、管理和缓存多个 Bot 实例。它实现了工厂模式，提供了统一的 Bot 实例管理接口。

## 📋 类概览

```php
namespace XBot\Telegram;

class BotManager implements BotManagerInterface
{
    // 管理多个 Bot 实例
    // 提供实例缓存和配置管理
    // 支持健康检查和统计监控
}
```

## 🏗️ 构造函数

### __construct()

创建 BotManager 实例并初始化配置。

```php
public function __construct(array $config = [])
```

#### 参数

| 参数 | 类型 | 必需 | 默认值 | 描述 |
|------|------|------|--------|------|
| `$config` | `array` | ❌ | `[]` | 全局配置数组 |

#### 配置选项

```php
$config = [
    'default_bot' => 'main',           // 默认 Bot 名称
    'cache' => [
        'enabled' => true,             // 启用实例缓存
        'ttl' => 3600,                // 缓存时间（秒）
    ],
    'logging' => [
        'enabled' => true,             // 启用日志
        'level' => 'info',            // 日志级别
    ],
    'stats' => [
        'enabled' => true,             // 启用统计
        'track_requests' => true,      // 跟踪请求统计
    ],
];
```

#### 示例

```php
use XBot\Telegram\BotManager;

// 使用默认配置
$manager = new BotManager();

// 使用自定义配置
$manager = new BotManager([
    'default_bot' => 'customer-service',
    'cache' => ['enabled' => false],
    'logging' => ['level' => 'debug'],
]);
```

## 🔧 核心方法

### bot()

获取指定名称的 Bot 实例，如果实例不存在则创建新实例。

```php
public function bot(string $name = null): TelegramBot
```

#### 参数

| 参数 | 类型 | 必需 | 默认值 | 描述 |
|------|------|------|--------|------|
| `$name` | `string` | ❌ | `null` | Bot 名称，为空时使用默认 Bot |

#### 返回值

- **类型**: `TelegramBot`
- **描述**: Bot 实例对象

#### 异常

| 异常类型 | 触发条件 |
|----------|----------|
| `ConfigurationException` | Bot 配置不存在或无效 |
| `HttpException` | HTTP 客户端创建失败 |

#### 示例

```php
// 获取默认 Bot
$defaultBot = $manager->bot();

// 获取指定 Bot
$customerBot = $manager->bot('customer-service');
$notifyBot = $manager->bot('notifications');

// 使用 Bot 实例
$message = $defaultBot->sendMessage(123456789, 'Hello!');
```

### createBot()

创建新的 Bot 实例，无论是否已存在同名实例。

```php
public function createBot(
    string $name,
    HttpClientInterface $httpClient,
    array $config = []
): TelegramBot
```

#### 参数

| 参数 | 类型 | 必需 | 描述 |
|------|------|------|------|
| `$name` | `string` | ✅ | Bot 实例名称 |
| `$httpClient` | `HttpClientInterface` | ✅ | HTTP 客户端实例 |
| `$config` | `array` | ❌ | Bot 特定配置 |

#### 返回值

- **类型**: `TelegramBot`
- **描述**: 新创建的 Bot 实例

#### 示例

```php
use XBot\Telegram\Http\GuzzleHttpClient;

// 创建 HTTP 客户端
$httpClient = new GuzzleHttpClient('BOT_TOKEN');

// 创建 Bot 实例
$bot = $manager->createBot('my-bot', $httpClient, [
    'cache' => ['enabled' => true],
    'timeout' => 30,
]);

// 使用创建的 Bot
$botInfo = $bot->getMe();
echo "Bot 用户名: @{$botInfo->username}";
```

### getBotConfig()

获取指定 Bot 的配置信息。

```php
public function getBotConfig(string $name): array
```

#### 参数

| 参数 | 类型 | 必需 | 描述 |
|------|------|------|------|
| `$name` | `string` | ✅ | Bot 名称 |

#### 返回值

- **类型**: `array`
- **描述**: Bot 配置数组

#### 异常

| 异常类型 | 触发条件 |
|----------|----------|
| `ConfigurationException` | 指定的 Bot 配置不存在 |

#### 示例

```php
// 获取 Bot 配置
$config = $manager->getBotConfig('main');

echo "Token: " . $config['token'];
echo "Webhook URL: " . $config['webhook']['url'];
```

### getInstance()

获取已创建的 Bot 实例，如果不存在则返回 null。

```php
public function getInstance(string $name): ?TelegramBot
```

#### 参数

| 参数 | 类型 | 必需 | 描述 |
|------|------|------|------|
| `$name` | `string` | ✅ | Bot 名称 |

#### 返回值

- **类型**: `TelegramBot|null`
- **描述**: Bot 实例或 null

#### 示例

```php
// 检查实例是否存在
$bot = $manager->getInstance('customer-service');

if ($bot !== null) {
    echo "Bot 实例已存在";
    $bot->sendMessage(123456789, 'Instance exists!');
} else {
    echo "Bot 实例不存在，需要创建";
}
```

## 📊 管理方法

### getAllInstances()

获取所有已创建的 Bot 实例。

```php
public function getAllInstances(): array
```

#### 返回值

- **类型**: `array<string, TelegramBot>`
- **描述**: Bot 名称 => Bot 实例的关联数组

#### 示例

```php
$instances = $manager->getAllInstances();

foreach ($instances as $name => $bot) {
    echo "Bot: $name\n";
    try {
        $info = $bot->getMe();
        echo "  用户名: @{$info->username}\n";
        echo "  状态: 在线\n";
    } catch (Exception $e) {
        echo "  状态: 离线 - {$e->getMessage()}\n";
    }
}
```

### getInstanceNames()

获取所有已创建的 Bot 实例名称。

```php
public function getInstanceNames(): array
```

#### 返回值

- **类型**: `array<string>`
- **描述**: Bot 名称数组

#### 示例

```php
$names = $manager->getInstanceNames();
echo "已创建的 Bot 实例: " . implode(', ', $names);
// 输出: 已创建的 Bot 实例: main, customer-service, notifications
```

### hasInstance()

检查指定名称的 Bot 实例是否存在。

```php
public function hasInstance(string $name): bool
```

#### 参数

| 参数 | 类型 | 必需 | 描述 |
|------|------|------|------|
| `$name` | `string` | ✅ | Bot 名称 |

#### 返回值

- **类型**: `bool`
- **描述**: 实例是否存在

#### 示例

```php
if ($manager->hasInstance('notifications')) {
    $manager->bot('notifications')->sendMessage(
        123456789, 
        '通知 Bot 已准备就绪'
    );
} else {
    echo "通知 Bot 尚未创建";
}
```

### removeInstance()

移除指定的 Bot 实例。

```php
public function removeInstance(string $name): bool
```

#### 参数

| 参数 | 类型 | 必需 | 描述 |
|------|------|------|------|
| `$name` | `string` | ✅ | Bot 名称 |

#### 返回值

- **类型**: `bool`
- **描述**: 是否成功移除

#### 示例

```php
// 移除 Bot 实例
if ($manager->removeInstance('old-bot')) {
    echo "Bot 实例已移除";
} else {
    echo "Bot 实例不存在或移除失败";
}
```

## 🔍 健康检查

### healthCheck()

对所有 Bot 实例进行健康检查。

```php
public function healthCheck(): array
```

#### 返回值

- **类型**: `array`
- **描述**: 健康检查结果

#### 结果格式

```php
[
    'overall_status' => 'healthy|degraded|unhealthy',
    'total_bots' => 3,
    'healthy_bots' => 2,
    'unhealthy_bots' => 1,
    'bots' => [
        'main' => [
            'status' => 'healthy',
            'response_time' => 150, // 毫秒
            'last_check' => '2024-03-15 10:30:00',
        ],
        'customer-service' => [
            'status' => 'healthy',
            'response_time' => 200,
            'last_check' => '2024-03-15 10:30:00',
        ],
        'notifications' => [
            'status' => 'unhealthy',
            'error' => 'Connection timeout',
            'last_check' => '2024-03-15 10:30:00',
        ],
    ],
]
```

#### 示例

```php
$health = $manager->healthCheck();

echo "总体状态: {$health['overall_status']}\n";
echo "健康的 Bot: {$health['healthy_bots']}/{$health['total_bots']}\n";

foreach ($health['bots'] as $name => $status) {
    $emoji = $status['status'] === 'healthy' ? '✅' : '❌';
    echo "$emoji $name: {$status['status']}\n";
    
    if (isset($status['response_time'])) {
        echo "  响应时间: {$status['response_time']}ms\n";
    }
    
    if (isset($status['error'])) {
        echo "  错误: {$status['error']}\n";
    }
}
```

### checkBotHealth()

检查单个 Bot 的健康状态。

```php
public function checkBotHealth(string $name): array
```

#### 参数

| 参数 | 类型 | 必需 | 描述 |
|------|------|------|------|
| `$name` | `string` | ✅ | Bot 名称 |

#### 返回值

- **类型**: `array`
- **描述**: 单个 Bot 的健康状态

#### 示例

```php
$health = $manager->checkBotHealth('main');

if ($health['status'] === 'healthy') {
    echo "✅ Bot 运行正常";
    echo "响应时间: {$health['response_time']}ms";
} else {
    echo "❌ Bot 存在问题: {$health['error']}";
}
```

## 📈 统计功能

### getStats()

获取所有 Bot 的统计信息。

```php
public function getStats(): array
```

#### 返回值

- **类型**: `array`
- **描述**: 统计信息数组

#### 统计格式

```php
[
    'manager' => [
        'created_at' => '2024-03-15 10:00:00',
        'uptime' => 1800, // 秒
        'total_instances' => 3,
        'active_instances' => 2,
    ],
    'bots' => [
        'main' => [
            'requests_total' => 1250,
            'requests_success' => 1200,
            'requests_failed' => 50,
            'success_rate' => 96.0,
            'avg_response_time' => 180,
            'last_request' => '2024-03-15 10:29:45',
        ],
        // ... 其他 Bot 统计
    ],
    'totals' => [
        'requests_total' => 3500,
        'requests_success' => 3350,
        'requests_failed' => 150,
        'success_rate' => 95.7,
    ],
]
```

#### 示例

```php
$stats = $manager->getStats();

echo "🤖 Bot Manager 统计\n";
echo "运行时间: {$stats['manager']['uptime']} 秒\n";
echo "总实例数: {$stats['manager']['total_instances']}\n";
echo "活跃实例: {$stats['manager']['active_instances']}\n\n";

echo "📊 总体统计\n";
echo "总请求数: {$stats['totals']['requests_total']}\n";
echo "成功率: {$stats['totals']['success_rate']}%\n\n";

echo "🔍 Bot 详细统计\n";
foreach ($stats['bots'] as $name => $botStats) {
    echo "$name:\n";
    echo "  请求数: {$botStats['requests_total']}\n";
    echo "  成功率: {$botStats['success_rate']}%\n";
    echo "  平均响应时间: {$botStats['avg_response_time']}ms\n";
}
```

### getBotStats()

获取单个 Bot 的统计信息。

```php
public function getBotStats(string $name): array
```

#### 参数

| 参数 | 类型 | 必需 | 描述 |
|------|------|------|------|
| `$name` | `string` | ✅ | Bot 名称 |

#### 返回值

- **类型**: `array`
- **描述**: 单个 Bot 的统计信息

#### 示例

```php
$stats = $manager->getBotStats('main');

echo "📈 Bot 'main' 统计信息\n";
echo "总请求: {$stats['requests_total']}\n";
echo "成功: {$stats['requests_success']}\n";
echo "失败: {$stats['requests_failed']}\n";
echo "成功率: {$stats['success_rate']}%\n";
echo "平均响应时间: {$stats['avg_response_time']}ms\n";
```

## ⚙️ 配置方法

### setDefaultBot()

设置默认 Bot 名称。

```php
public function setDefaultBot(string $name): void
```

#### 参数

| 参数 | 类型 | 必需 | 描述 |
|------|------|------|------|
| `$name` | `string` | ✅ | 默认 Bot 名称 |

#### 示例

```php
// 设置默认 Bot
$manager->setDefaultBot('customer-service');

// 现在调用 bot() 方法会返回 customer-service Bot
$bot = $manager->bot(); // 相当于 $manager->bot('customer-service')
```

### getDefaultBot()

获取当前默认 Bot 名称。

```php
public function getDefaultBot(): string
```

#### 返回值

- **类型**: `string`
- **描述**: 默认 Bot 名称

#### 示例

```php
$defaultName = $manager->getDefaultBot();
echo "当前默认 Bot: $defaultName";
```

### updateConfig()

更新全局配置。

```php
public function updateConfig(array $config): void
```

#### 参数

| 参数 | 类型 | 必需 | 描述 |
|------|------|------|------|
| `$config` | `array` | ✅ | 新的配置数组 |

#### 示例

```php
// 更新配置
$manager->updateConfig([
    'cache' => ['enabled' => false],
    'logging' => ['level' => 'error'],
]);
```

## 🔄 Laravel 集成

### 在 Laravel 中使用

BotManager 与 Laravel 深度集成，可以通过服务容器和 Facade 使用：

```php
// 使用服务容器
$manager = app(BotManager::class);

// 使用 Facade
use XBot\Telegram\Facades\Telegram;

// 获取默认 Bot
$bot = Telegram::bot();

// 获取指定 Bot
$customerBot = Telegram::bot('customer-service');

// 直接调用方法（使用默认 Bot）
$message = Telegram::sendMessage(123456789, 'Hello from Laravel!');
```

### 在服务提供者中注册

```php
// app/Providers/AppServiceProvider.php
use XBot\Telegram\BotManager;

public function register()
{
    $this->app->singleton(BotManager::class, function ($app) {
        return new BotManager(config('telegram'));
    });
}
```

## 🛠️ 实用示例

### 示例 1：多 Bot 协作

```php
$manager = new BotManager();

// 客服 Bot 处理用户询问
$customerBot = $manager->bot('customer-service');
$customerBot->sendMessage($userChatId, '您好！有什么可以帮助您的吗？');

// 通知 Bot 发送内部通知
$notifyBot = $manager->bot('notifications');
$notifyBot->sendMessage($adminChatId, "新用户 $userId 开始咨询");

// 日志 Bot 记录操作
$logBot = $manager->bot('logger');
$logBot->sendMessage($logChatId, "客服会话开始: User $userId");
```

### 示例 2：批量健康检查

```php
// 定期健康检查
$health = $manager->healthCheck();

if ($health['overall_status'] !== 'healthy') {
    // 发送告警
    $alertBot = $manager->bot('alerts');
    
    $message = "🚨 Bot 健康检查异常\n";
    $message .= "健康 Bot: {$health['healthy_bots']}/{$health['total_bots']}\n\n";
    
    foreach ($health['bots'] as $name => $status) {
        if ($status['status'] !== 'healthy') {
            $message .= "❌ $name: {$status['error']}\n";
        }
    }
    
    $alertBot->sendMessage($adminChatId, $message);
}
```

### 示例 3：统计监控

```php
// 生成每日统计报告
$stats = $manager->getStats();

$report = "📊 Bot 每日统计报告\n\n";
$report .= "⏱️ 运行时间: " . gmdate('H:i:s', $stats['manager']['uptime']) . "\n";
$report .= "🤖 活跃实例: {$stats['manager']['active_instances']}\n";
$report .= "📈 总请求数: {$stats['totals']['requests_total']}\n";
$report .= "✅ 成功率: {$stats['totals']['success_rate']}%\n\n";

$report .= "📋 Bot 详情:\n";
foreach ($stats['bots'] as $name => $botStats) {
    $report .= "• $name: {$botStats['requests_total']} 请求, ";
    $report .= "{$botStats['success_rate']}% 成功率\n";
}

$manager->bot('reports')->sendMessage($reportChatId, $report);
```

## 🔍 最佳实践

### 1. 实例命名规范

```php
// 推荐的命名规范
$manager->bot('customer-service');    // 客服 Bot
$manager->bot('notifications');       // 通知 Bot
$manager->bot('admin-tools');        // 管理工具 Bot
$manager->bot('analytics');          // 分析 Bot

// 避免的命名方式
$manager->bot('bot1');               // 不够描述性
$manager->bot('temp');               // 临时名称
```

### 2. 错误处理

```php
try {
    $bot = $manager->bot('customer-service');
    $message = $bot->sendMessage($chatId, $text);
} catch (ConfigurationException $e) {
    // Bot 配置错误
    Log::error("Bot 配置错误: " . $e->getMessage());
} catch (HttpException $e) {
    // 网络错误
    Log::warning("网络错误: " . $e->getMessage());
} catch (Exception $e) {
    // 其他错误
    Log::error("未知错误: " . $e->getMessage());
}
```

### 3. 资源管理

```php
// 在长时间运行的脚本中定期清理
if (count($manager->getAllInstances()) > 10) {
    // 移除不活跃的实例
    foreach ($manager->getInstanceNames() as $name) {
        $health = $manager->checkBotHealth($name);
        if ($health['status'] === 'unhealthy') {
            $manager->removeInstance($name);
        }
    }
}
```

---

🚀 **BotManager** 是管理多个 Bot 实例的强大工具，通过合理使用其 API，您可以构建出高效、可维护的 Telegram Bot 应用！