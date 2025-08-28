# GitHub Pages 用户手册文档设计

## 概述

设计并实现基于 docsify-themeable 的 GitHub Pages 用户手册文档系统，为 Telegram Bot PHP SDK 提供完整、用户友好的在线文档。该文档将覆盖从快速入门到高级特性的所有内容，支持多语言、搜索、版本控制等功能。

## 技术栈

- **文档框架**: Docsify
- **主题**: docsify-themeable 
- **托管平台**: GitHub Pages
- **构建工具**: GitHub Actions
- **搜索功能**: docsify-search
- **代码高亮**: Prism.js
- **图表支持**: Mermaid.js

## 项目结构

```
docs/
├── README.md                 # 首页内容
├── index.html               # Docsify 配置页面
├── _sidebar.md             # 侧边栏导航
├── _navbar.md              # 顶部导航栏
├── _coverpage.md           # 封面页
├── .nojekyll               # 禁用 Jekyll
├── CNAME                   # 自定义域名 (可选)
├── guide/                  # 指南文档
│   ├── README.md           # 指南首页
│   ├── installation.md    # 安装指南
│   ├── quick-start.md      # 快速开始
│   ├── configuration.md   # 配置说明
│   └── deployment.md      # 部署指南
├── api/                    # API 参考
│   ├── README.md           # API 首页
│   ├── bot-manager.md      # BotManager API
│   ├── telegram-bot.md    # TelegramBot API
│   ├── methods/           # API 方法
│   │   ├── message.md     # 消息方法
│   │   ├── chat.md        # 聊天方法
│   │   └── update.md      # 更新方法
│   └── models/            # 数据模型
│       ├── message.md     # Message 模型
│       ├── chat.md        # Chat 模型
│       └── user.md        # User 模型
├── examples/              # 使用示例
│   ├── README.md          # 示例首页
│   ├── basic-usage.md     # 基础使用
│   ├── laravel-integration.md # Laravel 集成
│   ├── webhook-handling.md    # Webhook 处理
│   └── advanced-features.md   # 高级特性
├── troubleshooting/       # 故障排除
│   ├── README.md          # 故障排除首页
│   ├── common-issues.md   # 常见问题
│   ├── error-codes.md     # 错误代码
│   └── debugging.md      # 调试指南
├── best-practices/        # 最佳实践
│   ├── README.md          # 最佳实践首页
│   ├── security.md        # 安全实践
│   ├── performance.md     # 性能优化
│   └── testing.md         # 测试策略
└── assets/               # 静态资源
    ├── css/              # 自定义样式
    ├── js/               # 自定义脚本
    └── images/           # 图片资源
```

## 核心文档内容架构

### 1. 首页 (README.md)

```markdown
# Telegram Bot PHP SDK

欢迎使用 Telegram Bot PHP SDK！这是一个功能强大、易于使用的 PHP 库，用于创建和管理 Telegram 机器人。

## 🚀 快速开始

- [安装指南](guide/installation.md)
- [快速开始](guide/quick-start.md)
- [配置说明](guide/configuration.md)

## 📖 文档导航

- [📘 用户指南](guide/) - 完整的使用指南
- [📋 API 参考](api/) - 详细的 API 文档
- [💡 示例代码](examples/) - 实用的代码示例
- [🔧 故障排除](troubleshooting/) - 问题解决方案
- [⭐ 最佳实践](best-practices/) - 专业建议

## ✨ 主要特性

- 🤖 多 Bot 支持
- 🔒 实例隔离
- ⚡ 高性能
- 🛡️ 类型安全
- 🔄 智能重试
- 📊 统计监控
```

### 2. 侧边栏导航 (_sidebar.md)

```markdown
- [首页](/)

- **用户指南**
  - [安装指南](guide/installation.md)
  - [快速开始](guide/quick-start.md)
  - [配置说明](guide/configuration.md)
  - [部署指南](guide/deployment.md)

- **API 参考**
  - [概览](api/)
  - [BotManager](api/bot-manager.md)
  - [TelegramBot](api/telegram-bot.md)
  - **API 方法**
    - [消息方法](api/methods/message.md)
    - [聊天方法](api/methods/chat.md)
    - [更新方法](api/methods/update.md)
  - **数据模型**
    - [Message](api/models/message.md)
    - [Chat](api/models/chat.md)
    - [User](api/models/user.md)

- **使用示例**
  - [基础使用](examples/basic-usage.md)
  - [Laravel 集成](examples/laravel-integration.md)
  - [Webhook 处理](examples/webhook-handling.md)
  - [高级特性](examples/advanced-features.md)

- **故障排除**
  - [常见问题](troubleshooting/common-issues.md)
  - [错误代码](troubleshooting/error-codes.md)
  - [调试指南](troubleshooting/debugging.md)

- **最佳实践**
  - [安全实践](best-practices/security.md)
  - [性能优化](best-practices/performance.md)
  - [测试策略](best-practices/testing.md)
```

### 3. 顶部导航栏 (_navbar.md)

```markdown
- 链接
  - [GitHub](https://github.com/xbot-my/telegram-sdk)
  - [Packagist](https://packagist.org/packages/xbot-my/telegram-sdk)
  - [问题反馈](https://github.com/xbot-my/telegram-sdk/issues)

- 语言
  - [:cn: 中文](/zh-cn/)
  - [:uk: English](/)
```

## Docsify 配置 (index.html)

```html
<!DOCTYPE html>
<html lang="zh-CN">
<head>
  <meta charset="UTF-8">
  <title>Telegram Bot PHP SDK</title>
  <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
  <meta name="description" content="功能强大、易于使用的 PHP Telegram Bot API SDK">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0">
  
  <!-- Theme CSS -->
  <link rel="stylesheet" href="//cdn.jsdelivr.net/npm/docsify-themeable@0/dist/css/theme-simple.css">
  
  <!-- Custom CSS -->
  <link rel="stylesheet" href="assets/css/custom.css">
  
  <!-- Favicon -->
  <link rel="icon" type="image/png" href="assets/images/favicon.png">
</head>
<body>
  <div id="app">加载中...</div>
  <script>
    window.$docsify = {
      name: 'Telegram Bot PHP SDK',
      repo: 'https://github.com/xbot-my/telegram-sdk',
      homepage: 'README.md',
      loadSidebar: true,
      loadNavbar: true,
      coverpage: true,
      autoHeader: true,
      subMaxLevel: 3,
      maxLevel: 4,
      
      // 搜索配置
      search: {
        placeholder: '搜索文档...',
        noData: '未找到结果',
        depth: 6,
        hideOtherSidebarContent: false,
      },
      
      // 分页配置
      pagination: {
        previousText: '上一章',
        nextText: '下一章',
        crossChapter: true,
        crossChapterText: true,
      },
      
      // 复制代码
      copyCode: {
        buttonText: '复制',
        errorText: '错误',
        successText: '已复制'
      },
      
      // 标签页
      tabs: {
        persist: true,
        sync: true,
        theme: 'classic',
        tabComments: true,
        tabHeadings: true
      },
      
      // 字数统计
      count: {
        countable: true,
        fontsize: '0.9em',
        color: 'rgb(90,90,90)',
        language: 'chinese'
      },
      
      // 代码高亮
      prism: {
        languages: ['php', 'bash', 'json', 'yaml']
      },
      
      // 主题配置
      themeable: {
        readyTransition: true,
        responsiveTables: true
      }
    }
  </script>
  
  <!-- Docsify 核心 -->
  <script src="//cdn.jsdelivr.net/npm/docsify@4"></script>
  
  <!-- 搜索插件 -->
  <script src="//cdn.jsdelivr.net/npm/docsify/lib/plugins/search.min.js"></script>
  
  <!-- 复制代码插件 -->
  <script src="//cdn.jsdelivr.net/npm/docsify-copy-code@2"></script>
  
  <!-- 分页插件 -->
  <script src="//cdn.jsdelivr.net/npm/docsify-pagination@2"></script>
  
  <!-- 标签页插件 -->
  <script src="//cdn.jsdelivr.net/npm/docsify-tabs@1"></script>
  
  <!-- 字数统计插件 -->
  <script src="//cdn.jsdelivr.net/npm/docsify-count@3"></script>
  
  <!-- 主题插件 -->
  <script src="//cdn.jsdelivr.net/npm/docsify-themeable@0"></script>
  
  <!-- 代码高亮 -->
  <script src="//cdn.jsdelivr.net/npm/prismjs@1/components/prism-php.min.js"></script>
  <script src="//cdn.jsdelivr.net/npm/prismjs@1/components/prism-bash.min.js"></script>
  <script src="//cdn.jsdelivr.net/npm/prismjs@1/components/prism-json.min.js"></script>
  <script src="//cdn.jsdelivr.net/npm/prismjs@1/components/prism-yaml.min.js"></script>
  
  <!-- Mermaid 图表支持 -->
  <script src="//cdn.jsdelivr.net/npm/mermaid@9/dist/mermaid.min.js"></script>
  <script src="//cdn.jsdelivr.net/npm/docsify-mermaid@2/dist/docsify-mermaid.js"></script>
  
  <!-- 自定义脚本 -->
  <script src="assets/js/custom.js"></script>
</body>
</html>
```

## 主题定制

### 自定义样式 (assets/css/custom.css)

```css
:root {
  /* 主色调 */
  --theme-color: #0088cc;
  --theme-color-secondary: #005580;
  
  /* 背景色 */
  --background: #ffffff;
  --sidebar-background: #f8f9fa;
  
  /* 文本色 */
  --text-color-base: #333333;
  --text-color-secondary: #666666;
  
  /* 边框色 */
  --border-color: #e1e4e8;
  
  /* 代码块 */
  --code-background: #f6f8fa;
  --code-border-color: #e1e4e8;
  
  /* 导航栏 */
  --navbar-background: var(--theme-color);
  --navbar-text-color: #ffffff;
}

/* 自定义 Logo */
.app-name-link {
  color: var(--theme-color) !important;
  font-weight: bold;
  font-size: 1.2em;
}

/* 侧边栏样式 */
.sidebar {
  border-right: 1px solid var(--border-color);
}

.sidebar ul li a {
  border-radius: 4px;
  transition: all 0.2s ease;
}

.sidebar ul li a:hover {
  background-color: var(--theme-color);
  color: white;
}

/* 代码块增强 */
pre[data-lang] {
  border: 1px solid var(--code-border-color);
  border-radius: 6px;
  background: var(--code-background);
}

/* 表格样式 */
table {
  border-collapse: collapse;
  border: 1px solid var(--border-color);
  border-radius: 6px;
  overflow: hidden;
}

th {
  background-color: var(--sidebar-background);
  font-weight: 600;
}

/* 警告框样式 */
.docsify-tabs__tab--active {
  background: var(--theme-color);
  color: white;
}

/* 响应式设计 */
@media screen and (max-width: 768px) {
  .sidebar {
    transform: translateX(-100%);
  }
  
  .sidebar-toggle {
    background-color: var(--theme-color);
  }
}

/* API 方法标记 */
.api-method {
  display: inline-block;
  padding: 2px 8px;
  border-radius: 4px;
  font-size: 0.8em;
  font-weight: bold;
  margin-right: 8px;
}

.api-method.get { background: #28a745; color: white; }
.api-method.post { background: #007bff; color: white; }
.api-method.put { background: #ffc107; color: black; }
.api-method.delete { background: #dc3545; color: white; }

/* 特性标签 */
.feature-tag {
  display: inline-block;
  padding: 2px 6px;
  background: var(--theme-color);
  color: white;
  border-radius: 12px;
  font-size: 0.75em;
  margin: 2px;
}
```

## GitHub Actions 自动部署

```yaml
# .github/workflows/docs.yml
name: Build and Deploy Docs

on:
  push:
    branches: [ main ]
    paths: [ 'docs/**' ]
  pull_request:
    branches: [ main ]
    paths: [ 'docs/**' ]

jobs:
  deploy:
    runs-on: ubuntu-latest
    
    steps:
    - name: Checkout
      uses: actions/checkout@v3
      with:
        fetch-depth: 0

    - name: Setup Node.js
      uses: actions/setup-node@v3
      with:
        node-version: '18'
        cache: 'npm'

    - name: Install dependencies
      run: |
        cd docs
        npm install -g docsify-cli

    - name: Build docs
      run: |
        cd docs
        docsify serve . --port 3000 &
        sleep 5
        kill %1

    - name: Deploy to GitHub Pages
      if: github.ref == 'refs/heads/main'
      uses: peaceiris/actions-gh-pages@v3
      with:
        github_token: ${{ secrets.GITHUB_TOKEN }}
        publish_dir: ./docs
        enable_jekyll: false
        cname: docs.telegram-sdk.xbot.my  # 可选：自定义域名
```

## 文档内容模板

### API 文档模板

````markdown
# TelegramBot API

## 概述

`TelegramBot` 类是 SDK 的核心类，提供了与 Telegram Bot API 交互的所有方法。

## 构造函数

```php
public function __construct(
    string $name,
    HttpClientInterface $httpClient,
    array $config = []
)
```

### 参数

| 参数 | 类型 | 描述 |
|------|------|------|
| `$name` | `string` | Bot 实例名称 |
| `$httpClient` | `HttpClientInterface` | HTTP 客户端实例 |
| `$config` | `array` | 可选配置参数 |

## 消息方法

### sendMessage

<span class="api-method post">POST</span> 发送文本消息

```php
public function sendMessage(
    int|string $chatId,
    string $text,
    array $options = []
): Message
```

#### 参数

| 参数 | 类型 | 必需 | 描述 |
|------|------|------|------|
| `$chatId` | `int\|string` | ✅ | 目标聊天ID |
| `$text` | `string` | ✅ | 消息文本 |
| `$options` | `array` | ❌ | 额外选项 |

#### 返回值

返回 `Message` 对象，包含已发送消息的详细信息。

#### 示例

```php
// 基础用法
$message = $bot->sendMessage(12345, 'Hello, World!');

// 带格式的消息
$message = $bot->sendMessage(12345, '<b>粗体</b> 和 <i>斜体</i>', [
    'parse_mode' => 'HTML'
]);

// 带键盘的消息
$message = $bot->sendMessage(12345, '选择一个选项:', [
    'reply_markup' => [
        'inline_keyboard' => [
            [['text' => '选项 1', 'callback_data' => 'opt1']],
            [['text' => '选项 2', 'callback_data' => 'opt2']]
        ]
    ]
]);
```

#### 错误处理

```php
try {
    $message = $bot->sendMessage(12345, 'Hello!');
} catch (ApiException $e) {
    if ($e->getErrorCode() === 400) {
        echo "Bad Request: " . $e->getDescription();
    }
} catch (HttpException $e) {
    echo "Network Error: " . $e->getMessage();
}
```
````

### 使用示例模板

````markdown
# Laravel 集成示例

## 安装和配置

### 1. 安装包

```bash
composer require xbot-my/telegram-sdk
```

### 2. 发布配置

```bash
php artisan vendor:publish --provider="XBot\Telegram\Providers\TelegramServiceProvider"
```

### 3. 环境配置

```env
TELEGRAM_MAIN_BOT_TOKEN=123456789:AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA
TELEGRAM_MAIN_WEBHOOK_URL=https://yourapp.com/telegram/webhook/main
```

## 基础使用

### 控制器中使用

```php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use XBot\Telegram\Facades\Telegram;

class BotController extends Controller
{
    public function sendWelcome(Request $request)
    {
        $chatId = $request->input('chat_id');
        
        $message = Telegram::sendMessage($chatId, '欢迎使用我们的服务！');
        
        return response()->json([
            'success' => true,
            'message_id' => $message->messageId
        ]);
    }
}
```

### 服务中使用

```php
<?php

namespace App\Services;

use XBot\Telegram\BotManager;

class NotificationService
{
    public function __construct(
        private BotManager $botManager
    ) {}
    
    public function sendNotification(int $userId, string $message): void
    {
        $bot = $this->botManager->bot('notifications');
        $bot->sendMessage($userId, $message);
    }
}
```

## Webhook 处理

### 注册路由

```php
// routes/web.php
Route::post('/telegram/webhook/{bot}', [TelegramWebhookController::class, 'handle'])
    ->middleware(['api', 'telegram.webhook']);
```

### 处理更新

```php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use XBot\Telegram\Facades\Telegram;
use XBot\Telegram\Models\DTO\Update;

class TelegramWebhookController extends Controller
{
    public function handle(Request $request, string $botName)
    {
        $bot = Telegram::bot($botName);
        $update = Update::fromArray($request->all());
        
        if ($update->isMessage()) {
            $this->handleMessage($bot, $update->message);
        } elseif ($update->isCallbackQuery()) {
            $this->handleCallbackQuery($bot, $update->callbackQuery);
        }
        
        return response()->json(['ok' => true]);
    }
    
    private function handleMessage($bot, $message)
    {
        $text = $message->text;
        $chatId = $message->chat->id;
        
        if ($text === '/start') {
            $bot->sendMessage($chatId, '欢迎！输入 /help 查看帮助。');
        } elseif ($text === '/help') {
            $bot->sendMessage($chatId, '可用命令：\n/start - 开始\n/help - 帮助');
        } else {
            $bot->sendMessage($chatId, "您说了：{$text}");
        }
    }
}
```
````

## 部署配置

### GitHub Pages 设置

1. **启用 GitHub Pages**
   - 进入仓库 Settings
   - 找到 Pages 设置
   - Source 选择 "Deploy from a branch"
   - Branch 选择 "gh-pages"

2. **自定义域名 (可选)**
   - 添加 CNAME 文件
   - 配置 DNS 记录

3. **HTTPS 强制**
   - 启用 "Enforce HTTPS" 选项

### CDN 优化

使用 jsDelivr CDN 加速资源加载：

```html
<!-- Docsify -->
<script src="//cdn.jsdelivr.net/npm/docsify@4/lib/docsify.min.js"></script>

<!-- 主题 -->
<link rel="stylesheet" href="//cdn.jsdelivr.net/npm/docsify-themeable@0/dist/css/theme-simple.css">

<!-- 插件 -->
<script src="//cdn.jsdelivr.net/npm/docsify/lib/plugins/search.min.js"></script>
```

## 维护和更新策略

### 内容维护

1. **定期同步**
   - 与代码库保持同步
   - 及时更新 API 变更
   - 添加新功能文档

2. **版本管理**
   - 使用 Git 标签管理版本
   - 维护版本更新日志
   - 支持多版本文档

3. **质量控制**
   - 代码示例测试
   - 链接有效性检查
   - 文档准确性验证

### 用户反馈

1. **反馈渠道**
   - GitHub Issues
   - 文档评论系统
   - 社区讨论

2. **改进机制**
   - 定期收集反馈
   - 优化用户体验
   - 持续改进内容

## SEO 优化

### Meta 标签

```html
<meta name="description" content="功能强大、易于使用的 PHP Telegram Bot API SDK - 完整文档">
<meta name="keywords" content="Telegram, Bot, PHP, SDK, API, Laravel, 文档">
<meta name="author" content="XBot Team">

<!-- Open Graph -->
<meta property="og:title" content="Telegram Bot PHP SDK - 官方文档">
<meta property="og:description" content="功能强大、易于使用的 PHP Telegram Bot API SDK">
<meta property="og:image" content="/assets/images/og-image.png">
<meta property="og:url" content="https://docs.telegram-sdk.xbot.my">

<!-- Twitter Card -->
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="Telegram Bot PHP SDK">
<meta name="twitter:description" content="功能强大、易于使用的 PHP Telegram Bot API SDK">
```

### 结构化数据

```html
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "SoftwareApplication",
  "name": "Telegram Bot PHP SDK",
  "description": "功能强大、易于使用的 PHP Telegram Bot API SDK",
  "url": "https://docs.telegram-sdk.xbot.my",
  "author": {
    "@type": "Organization",
    "name": "XBot Team"
  },
  "programmingLanguage": "PHP"
}
</script>
```