# Chat Boosts 示例

**Chat Boosts** 允许用户为群组或频道提升排名，给予额外功能和曝光。SDK 实现了相关查询端点，示例如下，来自项目 README【736136284671642†L165-L169】。

## 查询用户在群组中的 Boosts

```php
// 查询指定用户在某个群组的 Boosts
$boosts = $bot->getUserChatBoosts($chatId, $userId)->toArray();
```

## 查询群组或频道的 Boosts

```php
// 查询群组的整体 Boosts 信息
$chatBoosts = $bot->getChatBoosts($chatId)->toArray();
```

获取的返回值包含 Boost 数量、到期时间等字段，具体结构可通过 `toArray()` 查看。你可以据此在应用中展示当前群组的 Boost 状态或制作排行榜。

## 小结

Boosts 功能是 Telegram 提升社群活跃度的一种手段，开发者可以使用这些端点实现积分榜、自动提醒等功能。更多业务规则请参考官方文档和平台政策。
