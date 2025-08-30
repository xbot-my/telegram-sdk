# Telegram Bot PHP SDK 设计文档

## 概述

Telegram Bot PHP SDK 是一个轻量级、易用的 PHP 库，旨在简化开发者与 Telegram Bot API 的集成。该 SDK 为 PHP 开发者提供了完整的 Telegram Bot API 封装，支持所有官方 API 方法，并提供了优雅的 Laravel 集成。

### 核心目标

- 提供完整的 Telegram Bot API 封装
- 支持现代 PHP 特性（PHP 8.3+）
- 遵循 PSR 标准和最佳实践
- 提供优雅的 Laravel 集成
- 支持异步操作和高性能 HTTP 客户端
- 提供类型安全的 API 调用
- **支持多 Token、多 Bot、多实例管理**
- **实例间完全隔离，互不干扰**

### 技术栈

- **语言**: PHP 8.3+
- **HTTP 客户端**: Guzzle 7.10+
- **框架集成**: Laravel 11/12
- **测试框架**: PestPHP 4.0
- **代码质量**: PHPStan + Larastan

## 架构设计

### 整体架构

```mermaid
graph TB
    A[Laravel Application] --> B[Telegram Facade]
    B --> C[BotManager 实例管理器]
    C --> D1[Bot Instance 1]
    C --> D2[Bot Instance 2]
    C --> D3[Bot Instance N]
    
    D1 --> E1[API Methods 1]
    D1 --> F1[HTTP Client 1]
    D2 --> E2[API Methods 2]
    D2 --> F2[HTTP Client 2]
    D3 --> E3[API Methods N]
    D3 --> F3[HTTP Client N]
    
    E1 --> G[Message Methods]
    E1 --> H[Update Methods]
    E1 --> I[Chat Methods]
    E2 --> G
    E2 --> H
    E2 --> I
    
    F1 --> J1[Guzzle Client 1]
    F2 --> J2[Guzzle Client 2]
    F3 --> J3[Guzzle Client N]
    
    K[Multi-Bot Configuration] --> C
    L[Service Provider] --> B
    
    subgraph "Instance Isolation"
        D1
        D2
        D3
    end
    
    subgraph "Shared Components"
        G
        H
        I
    end
```

### 分层架构

| 层级      | 组件                   | 职责                    |
|---------|----------------------|-----------------------|
| **门面层** | Telegram Facade      | 提供静态访问接口，支持多实例路由      |
| **管理层** | BotManager           | 管理多个 Bot 实例，提供实例隔离    |
| **实例层** | TelegramBot Instance | 单个 Bot 实例，独立的认证和配置    |
| **方法层** | API Methods          | 封装具体的 Telegram API 方法 |
| **传输层** | HTTP Client          | 处理 HTTP 请求和响应，每实例独立   |
| **数据层** | DTO/Models           | 数据传输对象和模型定义           |

## 核心组件设计

### 1. BotManager 多实例管理器

```mermaid
classDiagram
    class BotManager {
        -array $instances
        -array $configs
        +bot(string $name): TelegramBot
        +createBot(string $name, array $config): TelegramBot
        +hasBot(string $name): bool
        +removeBot(string $name): void
        +getAllBots(): array
        +getDefaultBot(): TelegramBot
    }
    
    class TelegramBot {
        -string $token
        -string $name
        -HttpClientInterface $httpClient
        -array $config
        +__construct(name, token, config)
        +sendMessage(chatId, text, options?)
        +getUpdates(options?)
        +getName(): string
        +getToken(): string
    }
    
    class BotConfig {
        +string $token
        +string $baseUrl
        +int $timeout
        +int $retryAttempts
        +array $middleware
        +array $webhookConfig
    }
    
    BotManager --> TelegramBot : manages multiple
    TelegramBot --> BotConfig : uses
```

### 2. 实例隔离机制

```mermaid
sequenceDiagram
    participant App as Application
    participant Manager as BotManager
    participant Bot1 as Bot Instance 1
    participant Bot2 as Bot Instance 2
    participant API1 as Telegram API (Bot1)
    participant API2 as Telegram API (Bot2)
    
    App->>Manager: bot('customer-service')
    Manager->>Bot1: getInstance()
    App->>Bot1: sendMessage(chat1, 'Hello')
    Bot1->>API1: POST with token1
    
    App->>Manager: bot('marketing')
    Manager->>Bot2: getInstance()
    App->>Bot2: sendMessage(chat2, 'News')
    Bot2->>API2: POST with token2
    
    Note over Bot1,Bot2: 完全独立的实例和配置
```

### 3. TelegramBot 客户端

```mermaid
classDiagram
    class TelegramBot {
        -string $token
        -HttpClientInterface $httpClient
        -string $baseUrl
        +__construct(token, httpClient?)
        +sendMessage(chatId, text, options?)
        +getUpdates(options?)
        +setWebhook(url, options?)
        +deleteWebhook()
        +getMe()
    }
    
    class MessageMethods {
        +sendMessage(chatId, text, options?)
        +editMessageText(chatId, messageId, text, options?)
        +deleteMessage(chatId, messageId)
        +forwardMessage(chatId, fromChatId, messageId, options?)
        +copyMessage(chatId, fromChatId, messageId, options?)
    }
    
    class UpdateMethods {
        +getUpdates(options?)
        +setWebhook(url, options?)
        +deleteWebhook()
        +getWebhookInfo()
    }
    
    class ChatMethods {
        +getChat(chatId)
        +getChatMember(chatId, userId)
        +getChatMemberCount(chatId)
        +banChatMember(chatId, userId, options?)
        +unbanChatMember(chatId, userId, options?)
    }
    
    TelegramBot --> MessageMethods
    TelegramBot --> UpdateMethods
    TelegramBot --> ChatMethods
```

### 2. HTTP 客户端封装

```mermaid
sequenceDiagram
    participant App as Application
    participant Client as TelegramBot
    participant Http as HttpClient
    participant API as Telegram API
    
    App->>Client: sendMessage(chatId, text)
    Client->>Client: validateParameters()
    Client->>Client: buildRequest()
    Client->>Http: sendRequest(request)
    Http->>API: POST /sendMessage
    API-->>Http: JSON Response
    Http-->>Client: Response Object
    Client->>Client: parseResponse()
    Client-->>App: Message DTO
```

### 3. 响应处理机制

| 响应类型     | 处理方式                 | 返回值        |
|----------|----------------------|------------|
| **成功响应** | 解析 result 字段         | 对应的 DTO 对象 |
| **错误响应** | 抛出 TelegramException | -          |
| **网络错误** | 抛出 HttpException     | -          |
| **超时**   | 重试机制 + 异常            | -          |

### 4. 数据传输对象（DTO）

```mermaid
classDiagram
    class Message {
        +int $messageId
        +User $from
        +Chat $chat
        +DateTime $date
        +string $text
        +MessageEntity[] $entities
        +toArray()
        +fromArray(data)
    }
    
    class User {
        +int $id
        +bool $isBot
        +string $firstName
        +string $lastName
        +string $username
        +string $languageCode
    }
    
    class Chat {
        +int $id
        +string $type
        +string $title
        +string $username
        +string $firstName
        +string $lastName
        +bool $allMembersAreAdministrators
    }
    
    Message --> User
    Message --> Chat
```

### 5. 实例生命周期管理

```mermaid
sequenceDiagram
    participant App as Application
    participant Manager as BotManager
    participant Factory as BotFactory
    participant Instance as Bot Instance
    participant Http as HTTP Client
    
    App->>Manager: bot('customer-service')
    Manager->>Manager: checkInstance('customer-service')
    
    alt Instance Exists
        Manager-->>App: return existing instance
    else Instance Not Found
        Manager->>Factory: createInstance(config)
        Factory->>Instance: new TelegramBot(config)
        Factory->>Http: createHttpClient(config)
        Instance->>Instance: initialize()
        Factory-->>Manager: return instance
        Manager->>Manager: storeInstance('customer-service', instance)
        Manager-->>App: return new instance
    end
    
    App->>Instance: sendMessage(...)
    Instance-->>App: response
```

### 6. 资源隔离机制

| 资源类型         | 隔离策略         | 实现方式               |
|--------------|--------------|--------------------|
| **HTTP 连接**  | 每个 Bot 独立连接池 | Guzzle Client 实例化  |
| **认证 Token** | Bot 级别存储     | 实例属性封装             |
| **配置信息**     | 按名称空间分组      | 配置数组结构             |
| **事件监听**     | 带 Bot 标识的事件  | Event Namespace    |
| **缓存数据**     | 按 Bot 名称分组   | Cache Key Prefix   |
| **日志记录**     | 带 Bot 上下文    | Contextual Logging |

## API 方法组织

### 方法分类

```mermaid
mindmap
  root((Telegram API))
    Messages
      sendMessage
      editMessageText
      deleteMessage
      forwardMessage
      copyMessage
      sendPhoto
      sendVideo
      sendDocument
    Updates
      getUpdates
      setWebhook
      deleteWebhook
      getWebhookInfo
    Chat Management
      getChat
      getChatMember
      banChatMember
      unbanChatMember
      setChatTitle
    Media
      sendPhoto
      sendVideo
      sendAnimation
      sendAudio
      sendDocument
      sendSticker
    Payments
      sendInvoice
      answerPreCheckoutQuery
      answerShippingQuery
    Inline Mode
      answerInlineQuery
      editMessageReplyMarkup
    Games
      sendGame
      setGameScore
      getGameHighScores
```

### 方法实现模式

| 方法类型     | 实现模式       | 示例                                   |
|----------|------------|--------------------------------------|
| **简单方法** | 直接 HTTP 调用 | `getMe()`, `deleteWebhook()`         |
| **参数丰富** | 构建器模式      | `sendMessage()`, `editMessageText()` |
| **文件上传** | 多部分表单      | `sendPhoto()`, `sendDocument()`      |
| **批量操作** | 数组参数       | `sendMediaGroup()`                   |

## Laravel 集成

### 多 Bot 服务提供者架构

```mermaid
graph LR
    A[TelegramServiceProvider] --> B[注册 BotManager]
    A --> C[绑定多实例]
    A --> D[注册门面]
    A --> E[发布配置文件]
    
    B --> F[BotManager 单例]
    C --> G[Bot Factory]
    D --> H[Telegram Facade]
    E --> I[artisan vendor:publish]
    
    subgraph "多 Bot 配置"
        J[default bot]
        K[customer-service bot]
        L[marketing bot]
        M[admin bot]
    end
    
    F --> J
    F --> K
    F --> L
    F --> M
```

### 多 Bot 配置管理

| 配置项                          | 类型     | 说明          | 示例                             |
|------------------------------|--------|-------------|--------------------------------|
| `default`                    | string | 默认 Bot 名称   | `'main'`                       |
| `bots.{name}.token`          | string | Bot Token   | `env('TELEGRAM_BOT_TOKEN')`    |
| `bots.{name}.base_url`       | string | API 基础 URL  | `https://api.telegram.org/bot` |
| `bots.{name}.timeout`        | int    | 请求超时时间（秒）   | 30                             |
| `bots.{name}.retry_attempts` | int    | 重试次数        | 3                              |
| `bots.{name}.webhook_url`    | string | Webhook URL | `null`                         |
| `bots.{name}.middleware`     | array  | 中间件配置       | `[]`                           |

### 配置文件示例

```php
// config/telegram.php
return [
    'default' => 'main',
    
    'bots' => [
        'main' => [
            'token' => env('TELEGRAM_MAIN_BOT_TOKEN'),
            'base_url' => 'https://api.telegram.org/bot',
            'timeout' => 30,
            'retry_attempts' => 3,
            'webhook_url' => env('TELEGRAM_MAIN_WEBHOOK_URL'),
            'middleware' => ['auth', 'rate_limit'],
        ],
        
        'customer-service' => [
            'token' => env('TELEGRAM_CS_BOT_TOKEN'),
            'base_url' => 'https://api.telegram.org/bot',
            'timeout' => 15,
            'retry_attempts' => 2,
            'webhook_url' => env('TELEGRAM_CS_WEBHOOK_URL'),
            'middleware' => ['auth'],
        ],
        
        'marketing' => [
            'token' => env('TELEGRAM_MARKETING_BOT_TOKEN'),
            'base_url' => 'https://api.telegram.org/bot',
            'timeout' => 60,
            'retry_attempts' => 5,
            'middleware' => ['rate_limit'],
        ],
    ],
];
```

### 多实例门面使用模式

```php
// 使用默认 Bot
Telegram::sendMessage($chatId, 'Hello World!');

// 使用指定名称的 Bot
Telegram::bot('customer-service')->sendMessage($chatId, '客服回复');
Telegram::bot('marketing')->sendMessage($chatId, '营销消息');

// 链式调用
Telegram::bot('admin')
    ->to($chatId)
    ->message('管理员通知')
    ->keyboard($keyboard)
    ->send();

// 批量操作不同 Bot
$customerBot = Telegram::bot('customer-service');
$marketingBot = Telegram::bot('marketing');

$customerBot->sendMessage($chat1, '客服消息 1');
$marketingBot->sendMessage($chat2, '营销消息 1');
$customerBot->sendMessage($chat1, '客服消息 2');
$marketingBot->sendMessage($chat2, '营销消息 2');
```

## 错误处理策略

### 异常层次结构

```mermaid
classDiagram
    class TelegramException {
        <<abstract>>
        +string $message
        +int $code
        +array $context
    }
    
    class ApiException {
        +int $errorCode
        +string $description
        +array $parameters
    }
    
    class HttpException {
        +int $statusCode
        +string $reason
    }
    
    class ValidationException {
        +array $errors
        +string $field
    }
    
    class RateLimitException {
        +int $retryAfter
        +DateTime $resetTime
    }
    
    TelegramException <|-- ApiException
    TelegramException <|-- HttpException
    TelegramException <|-- ValidationException
    TelegramException <|-- RateLimitException
```

### 错误处理流程

| 错误类型       | HTTP 状态码 | 处理策略                   |
|------------|----------|------------------------|
| **参数错误**   | 400      | 抛出 ValidationException |
| **认证失败**   | 401      | 抛出 ApiException        |
| **权限不足**   | 403      | 抛出 ApiException        |
| **资源不存在**  | 404      | 抛出 ApiException        |
| **请求过于频繁** | 429      | 实现退避重试                 |
| **服务器错误**  | 5xx      | 重试机制                   |

## 高级特性

### 1. 多实例 Webhook 处理

```mermaid
sequenceDiagram
    participant TG1 as Telegram Bot 1
    participant TG2 as Telegram Bot 2
    participant Router as Laravel Router
    participant Handler as WebhookHandler
    participant Manager as BotManager
    participant Bot1 as Bot Instance 1
    participant Bot2 as Bot Instance 2
    
    TG1->>Router: POST /webhook/customer-service
    Router->>Handler: handle('customer-service', request)
    Handler->>Manager: bot('customer-service')
    Manager->>Bot1: getInstance()
    Handler->>Bot1: processUpdate(update)
    Bot1-->>Handler: response
    Handler-->>Router: 200 OK
    
    TG2->>Router: POST /webhook/marketing
    Router->>Handler: handle('marketing', request)
    Handler->>Manager: bot('marketing')
    Manager->>Bot2: getInstance()
    Handler->>Bot2: processUpdate(update)
    Bot2-->>Handler: response
    Handler-->>Router: 200 OK
```

### 2. 实例隔离中间件系统

```mermaid
graph TB
    A[Bot Request] --> B{Bot Instance Router}
    B -->|customer-service| C[CS Middleware Stack]
    B -->|marketing| D[Marketing Middleware Stack]
    B -->|admin| E[Admin Middleware Stack]
    
    C --> C1[Auth Middleware]
    C1 --> C2[Rate Limit: 10/min]
    C2 --> C3[Logging Middleware]
    C3 --> F1[CS Bot Handler]
    
    D --> D1[Marketing Auth]
    D1 --> D2[Rate Limit: 100/min]
    D2 --> D3[Analytics Middleware]
    D3 --> F2[Marketing Bot Handler]
    
    E --> E1[Admin Auth]
    E1 --> E2[IP Whitelist]
    E2 --> E3[Audit Log]
    E3 --> F3[Admin Bot Handler]
```

### 3. 多实例事件系统

| 事件类型                    | 命名规则                          | 携带数据                         |
|-------------------------|-------------------------------|------------------------------|
| `MessageReceived`       | `{botName}.message.received`  | Bot Name + Message DTO       |
| `CallbackQueryReceived` | `{botName}.callback.received` | Bot Name + CallbackQuery DTO |
| `InlineQueryReceived`   | `{botName}.inline.received`   | Bot Name + InlineQuery DTO   |
| `WebhookUpdated`        | `{botName}.webhook.updated`   | Bot Name + Webhook Config    |

### 4. 实例状态管理

```mermaid
stateDiagram-v2
    [*] --> Inactive: Bot 未初始化
    Inactive --> Initializing: 创建实例
    Initializing --> Active: 初始化成功
    Initializing --> Error: 初始化失败
    Active --> Processing: 处理请求
    Processing --> Active: 处理完成
    Processing --> Error: 处理失败
    Active --> Suspended: 暂停服务
    Suspended --> Active: 恢复服务
    Active --> Inactive: 销毁实例
    Error --> Inactive: 重置实例
    
    note right of Active: 每个 Bot 实例独立维护状态
    note right of Processing: 并发处理不同 Bot 的请求
```

### 5. 性能优化机制

| 优化策略     | 实现方式               | 优势        |
|----------|--------------------|-----------|
| **实例复用** | 单例模式 + 懒加载         | 减少内存占用    |
| **连接池**  | 每个 Bot 独立 HTTP 连接池 | 提高并发性能    |
| **缓存策略** | 按 Bot 名称分组缓存       | 避免跨实例数据混淆 |
| **异步处理** | 支持 Promise/Future  | 提高处理效率    |

## 测试策略

### 多实例测试层次

```mermaid
pyramid
    title 多实例测试金字塔
    section Unit Tests
        BotManager 测试
        实例隔离测试
        API Methods
        DTO Classes
    section Integration Tests
        多 Bot HTTP 客户端
        Laravel 多实例集成
        Webhook 多路由
    section Feature Tests
        多 Bot 并发场景
        实例状态管理
        跨实例隔离性
```

### 测试工具配置

| 工具                      | 用途           | 多实例特性        |
|-------------------------|--------------|--------------|
| **PestPHP**             | 测试框架         | 支持并发测试多个 Bot |
| **Orchestra Testbench** | Laravel 测试环境 | 模拟多 Bot 配置   |
| **Mockery**             | Mock 对象      | 每个实例独立 Mock  |
| **PHPStan**             | 静态分析         | 检查类型安全和实例隔离  |

### 多实例 Mock 策略

```mermaid
graph TB
    A[测试套件] --> B[BotManager Mock]
    B --> C[Bot Instance 1 Mock]
    B --> D[Bot Instance 2 Mock]
    B --> E[Bot Instance N Mock]
    
    C --> F[HTTP Client Mock 1]
    D --> G[HTTP Client Mock 2]
    E --> H[HTTP Client Mock N]
    
    F --> I[预定义响应 1]
    G --> J[预定义响应 2]
    H --> K[预定义响应 N]
    
    subgraph "隔离测试"
        L[实例 A 测试]
        M[实例 B 测试]
        N[并发测试]
    end
```

### 关键测试用例

| 测试类型           | 测试内容            | 验证目标         |
|----------------|-----------------|--------------|
| **实例隔离**       | 不同 Bot 同时发送消息   | 互不干扰，数据不混淆   |
| **配置独立**       | 各 Bot 使用不同配置    | 配置不会相互影响     |
| **并发处理**       | 多 Bot 并发 API 调用 | 性能和稳定性       |
| **错误隔离**       | 一个 Bot 错误不影响其他  | 容错性和稳定性      |
| **内存管理**       | 实例创建和销毁         | 内存泄漏检测       |
| **Webhook 路由** | 多个 Webhook 端点   | 请求路由到正确的 Bot |
