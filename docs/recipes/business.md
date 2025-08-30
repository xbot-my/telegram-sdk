---
title: Business 示例
---

# Business 示例

Telegram 推出了 **Business API**，用于企业账户管理。XBot SDK 提供若干端点，帮助你读取与管理业务消息、设置企业名称和批准建议帖子。以下片段基于项目 README【736136284671642†L177-L183】。

## 读取和删除业务消息

```php
// 标记业务消息为已读
$bot->readBusinessMessage($chatId, $messageId);

// 删除多条业务消息
$bot->deleteBusinessMessages($chatId, [$messageId1, $messageId2]);
```

## 设置企业账户名称

```php
$bot->setBusinessAccountName('My Business');
```

## 批准和拒绝建议帖子

```php
// 批准建议帖子
$bot->approveSuggestedPost($chatId, $messageId);

// 拒绝建议帖子（可使用 declineSuggestedPost）
$bot->declineSuggestedPost($chatId, $messageId);
```

## 小结

Business API 对于企业运营机器人尤其重要，可以帮助你统一管理商业通信与社群帖子。更多高级功能（如客服对话、自动化营销）需参考 Telegram 的官方 Business 指南。