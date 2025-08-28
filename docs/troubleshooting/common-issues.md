# ❓ 常见问题

本文档收集了使用 Telegram Bot PHP SDK 过程中的常见问题和解决方案。

## 🔧 安装和配置问题

### Q1: 安装时提示 PHP 版本不兼容

**问题**: `requires php >=8.3.0 but your php version is 8.2.x`

**解决方案**:
1. 升级 PHP 到 8.3 或更高版本
2. 使用正确的 PHP 版本安装：
   ```bash
   /usr/bin/php8.3 /usr/local/bin/composer require xbot-my/telegram-sdk
   ```

### Q2: 缺少必需的 PHP 扩展

**问题**: `Extension curl is missing from your system`

**解决方案**:
```bash
# Ubuntu/Debian
sudo apt-get install php8.3-curl php8.3-json php8.3-mbstring

# CentOS/RHEL
sudo yum install php83-curl php83-json php83-mbstring

# macOS (Homebrew)
brew install php@8.3
```

### Q3: Laravel 自动发现失败

**问题**: 在 Laravel 中使用 Facade 时提示类不存在

**解决方案**:
```bash
# 清除配置缓存
php artisan config:clear

# 清除自动加载缓存
composer dump-autoload

# 重新发布配置
php artisan vendor:publish --provider="XBot\Telegram\Providers\TelegramServiceProvider" --force
```

## 🤖 Bot 配置问题

### Q4: Bot Token 格式错误

**问题**: `Invalid token format`

**解决方案**:
- 确保 Token 格式为 `数字:字母数字字符串`
- 示例: `123456789:AABBCCDDEEFFGGHHIIJJKKLLMMNNOOPPQQRRa`
- 从 BotFather 重新获取 Token

### Q5: 无法获取 Bot 信息

**问题**: 调用 `getMe()` 方法失败

**解决方案**:
```php
try {
    $botInfo = $bot->getMe();
    echo "Bot 连接正常: @{$botInfo->username}";
} catch (ApiException $e) {
    if ($e->getErrorCode() === 401) {
        echo "Token 无效，请检查 Bot Token";
    }
} catch (HttpException $e) {
    echo "网络连接问题: " . $e->getMessage();
}
```

### Q6: 多 Bot 配置冲突

**问题**: 多个 Bot 实例配置相互影响

**解决方案**:
```php
// 为每个 Bot 使用独立的配置
$customerBot = $manager->createBot('customer-service', $customerHttpClient, [
    'cache' => ['prefix' => 'customer:'],
    'timeout' => 30
]);

$notifyBot = $manager->createBot('notifications', $notifyHttpClient, [
    'cache' => ['prefix' => 'notify:'],
    'timeout' => 60
]);
```

## 🌐 网络和连接问题

### Q7: 连接超时

**问题**: `Connection timeout`

**解决方案**:
```php
// 增加超时时间
$httpClient = new GuzzleHttpClient($token, [
    'timeout' => 60,
    'connect_timeout' => 30
]);
```

### Q8: SSL 证书验证失败

**问题**: `SSL certificate verification failed`

**解决方案**:
```php
// 临时禁用 SSL 验证（仅开发环境）
$httpClient = new GuzzleHttpClient($token, [
    'verify' => false  // 生产环境不建议
]);

// 或指定证书路径
$httpClient = new GuzzleHttpClient($token, [
    'verify' => '/path/to/cacert.pem'
]);
```

### Q9: 代理配置

**问题**: 需要通过代理访问 Telegram API

**解决方案**:
```php
$httpClient = new GuzzleHttpClient($token, [
    'proxy' => [
        'http'  => 'http://proxy.example.com:8080',
        'https' => 'https://proxy.example.com:8080',
    ]
]);
```

## 📨 消息发送问题

### Q10: 消息发送失败

**问题**: `Bad Request: chat not found`

**解决方案**:
- 确保聊天 ID 正确
- 用户必须先与 Bot 发起对话
- 检查 Bot 是否被用户阻止

### Q11: 消息过长

**问题**: `Bad Request: message is too long`

**解决方案**:
```php
function sendLongMessage($bot, $chatId, $text) {
    $maxLength = 4096;
    
    if (strlen($text) <= $maxLength) {
        return $bot->sendMessage($chatId, $text);
    }
    
    // 分割长消息
    $chunks = str_split($text, $maxLength - 100); // 留些余量
    foreach ($chunks as $chunk) {
        $bot->sendMessage($chatId, $chunk);
        usleep(100000); // 避免速率限制
    }
}
```

### Q12: 特殊字符转义

**问题**: Markdown 格式中的特殊字符导致解析错误

**解决方案**:
```php
// MarkdownV2 需要转义的字符
function escapeMarkdownV2($text) {
    $chars = ['_', '*', '[', ']', '(', ')', '~', '`', '>', '#', '+', '-', '=', '|', '{', '}', '.', '!'];
    foreach ($chars as $char) {
        $text = str_replace($char, '\\' . $char, $text);
    }
    return $text;
}

$safeText = escapeMarkdownV2($userInput);
$bot->sendMessage($chatId, "*安全的文本*: " . $safeText, [
    'parse_mode' => 'MarkdownV2'
]);
```

## 🔄 Webhook 问题

### Q13: Webhook 设置失败

**问题**: `Bad Request: bad webhook: HTTPS url must be provided`

**解决方案**:
- 确保 Webhook URL 使用 HTTPS
- 验证 SSL 证书有效
- 检查端口是否为 443, 80, 88, 8443

### Q14: Webhook 接收不到数据

**问题**: 设置了 Webhook 但收不到更新

**解决方案**:
```php
// 检查 Webhook 状态
$webhookInfo = $bot->getWebhookInfo();
echo "URL: " . $webhookInfo['url'] . "\n";
echo "最后错误: " . ($webhookInfo['last_error_message'] ?? '无') . "\n";

// 确保正确处理 POST 数据
$input = file_get_contents('php://input');
$update = json_decode($input, true);

if ($update) {
    // 处理更新
    $updateObj = Update::fromArray($update);
}
```

### Q15: Webhook 验证失败

**问题**: 收到非法的 Webhook 请求

**解决方案**:
```php
// 验证 Webhook 来源
$secretToken = 'your-secret-token';
$receivedToken = $_SERVER['HTTP_X_TELEGRAM_BOT_API_SECRET_TOKEN'] ?? '';

if (!hash_equals($secretToken, $receivedToken)) {
    http_response_code(403);
    exit('Unauthorized');
}
```

## 📁 文件上传问题

### Q16: 文件大小限制

**问题**: `Bad Request: file too large`

**解决方案**:
```php
$filePath = '/path/to/file.pdf';
$fileSize = filesize($filePath);

// Telegram 限制
$maxSize = 50 * 1024 * 1024; // 50MB

if ($fileSize > $maxSize) {
    $bot->sendMessage($chatId, '文件过大，无法发送');
} else {
    $bot->sendDocument($chatId, fopen($filePath, 'r'));
}
```

### Q17: 文件类型不支持

**问题**: 某些文件类型无法发送

**解决方案**:
```php
$allowedTypes = ['pdf', 'doc', 'docx', 'jpg', 'png', 'mp4'];
$fileExtension = pathinfo($filePath, PATHINFO_EXTENSION);

if (!in_array(strtolower($fileExtension), $allowedTypes)) {
    $bot->sendMessage($chatId, '不支持的文件类型');
} else {
    $bot->sendDocument($chatId, fopen($filePath, 'r'));
}
```

## ⚡ 性能问题

### Q18: 速率限制

**问题**: `Too Many Requests: retry after X`

**解决方案**:
```php
function sendWithRetry($bot, $chatId, $text, $maxRetries = 3) {
    for ($i = 0; $i < $maxRetries; $i++) {
        try {
            return $bot->sendMessage($chatId, $text);
        } catch (ApiException $e) {
            if ($e->getErrorCode() === 429) {
                $retryAfter = $e->getRetryAfter() ?? 1;
                sleep($retryAfter);
                continue;
            }
            throw $e;
        }
    }
    throw new Exception('Max retries exceeded');
}
```

### Q19: 内存占用过高

**问题**: 处理大量更新时内存不足

**解决方案**:
```php
// 批量处理更新
$batchSize = 10;
$offset = 0;

while (true) {
    $updates = $bot->getUpdates([
        'offset' => $offset,
        'limit' => $batchSize
    ]);
    
    if (empty($updates)) {
        break;
    }
    
    foreach ($updates as $updateData) {
        // 处理单个更新
        processUpdate($updateData);
        $offset = $updateData['update_id'] + 1;
    }
    
    // 释放内存
    unset($updates);
    gc_collect_cycles();
}
```

## 🔒 安全问题

### Q20: 防止重放攻击

**问题**: 如何确保 Webhook 数据的唯一性

**解决方案**:
```php
// 使用 Redis 存储已处理的 update_id
$redis = new Redis();
$updateId = $update['update_id'];

if ($redis->exists("processed:$updateId")) {
    // 已处理过，忽略
    exit('OK');
}

// 处理更新
processUpdate($update);

// 标记为已处理（设置过期时间）
$redis->setex("processed:$updateId", 3600, '1');
```

## 🔍 调试技巧

### Q21: 启用调试模式

```php
// 启用详细日志
$httpClient = new GuzzleHttpClient($token, [
    'debug' => true,
    'timeout' => 30
]);

// 查看原始响应
$response = $bot->call('getMe', []);
echo "原始响应：\n";
print_r($response->getRawData());
```

### Q22: 日志记录

```php
use Psr\Log\LoggerInterface;

class TelegramLogger {
    private LoggerInterface $logger;
    
    public function logRequest(string $method, array $params) {
        $this->logger->info("Telegram API 请求", [
            'method' => $method,
            'params' => $params
        ]);
    }
    
    public function logResponse($response) {
        $this->logger->info("Telegram API 响应", [
            'response' => $response
        ]);
    }
}
```

## 💬 获取帮助

如果以上解决方案无法解决您的问题：

1. 📖 查看 [API 参考文档](../api/)
2. 🔍 搜索 [GitHub Issues](https://github.com/xbot-my/telegram-sdk/issues)
3. 💬 在 [讨论区](https://github.com/xbot-my/telegram-sdk/discussions) 提问
4. 🐛 [提交新的 Issue](https://github.com/xbot-my/telegram-sdk/issues/new)

---

💡 **提示**: 在提问时，请提供详细的错误信息、环境信息和代码示例，这样能更快得到帮助！