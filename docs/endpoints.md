# 端点说明

本节简要介绍 SDK 如何映射 Telegram Bot API 的方法，以及如何处理返回值。完整的端点列表请参见 [API 覆盖](api-coverage.md)。

## 方法与类映射

SDK 将每个 Bot API 方法映射为一个命名空间 `XBot\Telegram\API` 下的类。例如：

* `XBot\Telegram\API\GetMe` ↔ `getMe`
* `XBot\Telegram\API\SendMessage` ↔ `sendMessage`
* `XBot\Telegram\API\SetWebhook` ↔ `setWebhook`

映射规则是：以 Bot API 的方法名为基础，将首字母大写并转换为 StudlyCase 形成类名，再放在 `XBot\Telegram\API` 命名空间下【247764572816424†L0-L15】。SDK 同时在 `Bot` 实例上提供蛇形别名，例如 `$bot->get_webhook_info()` 会调用 `GetWebhookInfo` 类【247764572816424†L16-L23】。

如果官方 Bot API 发布了新的方法，只需在 `src/API` 目录中新增对应类，SDK 会自动通过魔术调用转发，使其可以直接通过 `$bot->methodName()` 调用【247764572816424†L143-L147】。

## 返回值 Transformer

调用端点方法时会返回一个响应对象，该对象实现了 `toArray()`、`toObject()` 和 `toJson()` 三个方法，便于根据需要转换为数组、对象或 JSON 字符串【736136284671642†L41-L53】。例如：

```php
$meArray = $bot->getMe()->toArray();
$meObj   = $bot->getMe()->toObject();
$meJson  = $bot->getMe()->toJson();
```

SDK 本身不对响应结果做持久化，你可以根据业务需求将其保存到数据库或缓存中。若需要集合式返回，可在你的项目中安装 `illuminate/support` 并使用 `->collection()` 方法，这一点也在项目 README 中提到【736136284671642†L218-L221】。

## 可选参数透传

所有端点都支持通过 `$options` 数组传入 Telegram 原生参数，未在 SDK 中预定义的字段会被直接透传【736136284671642†L188-L215】。这让 SDK 能够在 Telegram 更新 API 时保持兼容，而无需等待库更新。更多信息请阅读 [选项透传](../guide/options.md)。

## 小结

理解方法与类的映射规则，可以帮助你快速使用未在文档中列出的新接口。通过统一的返回转换器，你可以灵活地根据业务选择数组、对象或 JSON 格式。此外，SDK 不限制参数字段，方便你按官方文档自由组合请求。
