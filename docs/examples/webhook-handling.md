# 🔗 Webhook 处理（极简）

## 纯 PHP 示例

```php
<?php
require_once 'vendor/autoload.php';

use XBot\Telegram\Bot;

$bot = Bot::token('YOUR_BOT_TOKEN');

// 读取原始请求体
$raw = file_get_contents('php://input');
$data = json_decode($raw, true) ?: [];

if (isset($data['message'])) {
  $msg = $data['message'];
  $chatId = $msg['chat']['id'];
  $text = $msg['text'] ?? '';

  $bot->sendMessage($chatId, $text === '/start' ? '👋 欢迎！' : "你说了: $text");
}

http_response_code(200);
echo json_encode(['ok' => true]);
```

设置 Webhook：

```php
<?php
require_once 'vendor/autoload.php';

use XBot\Telegram\Bot;

$bot = Bot::token('YOUR_BOT_TOKEN');
$bot->setWebhook('https://your-domain.com/path/to/this-script.php');
```

提示：生产环境请使用 HTTPS；如启用签名校验，请确保服务端与设置 Webhook 时的 `secret_token` 一致。

