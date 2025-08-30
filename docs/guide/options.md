---
title: 选项透传
---

# Bot API 9.2 选项透传

自 Bot API 9.2 起，Telegram 在许多发送类方法中新增了一些可选参数，用于私信话题、建议帖子和清单任务。XBot Telegram SDK 对这些参数提供完全透传支持；你只需将它们写进第三个参数数组即可，SDK 会自动转成 JSON。

## direct_messages_topic_id

此参数用于将消息发送到频道的私信话题。适用于 `sendMessage`、`sendPhoto`、`sendVideo`、`sendAnimation`、`sendAudio`、`sendDocument`、`sendPaidMedia`、`sendSticker`、`sendVideoNote`、`sendVoice`、`sendLocation`、`sendVenue`、`sendContact`、`sendDice`、`sendInvoice`、`copyMessage`、`copyMessages` 和 `forwardMessage` 等方法【736136284671642†L188-L193】。

示例：

```php
// 发送到频道的私信话题
$bot->sendMessage($chatId, 'Hello topic', [
    'direct_messages_topic_id' => 1234,
]);
```

## suggested_post_parameters

当发送内容到频道并希望它作为“建议帖子”提交给频道管理员审批时，可以填写此参数【736136284671642†L194-L209】。具体字段请参考官方文档（包括价格、目标受众等）。

示例：

```php
$bot->sendPhoto($chatId, 'file_id_or_path', [
    'caption' => 'Hi',
    'suggested_post_parameters' => [
        // 在此填入建议帖子的额外字段
    ],
]);
```

## reply_parameters.checklist_task_id

支持发送信息回复到特定的清单任务。在回复内容中嵌套 `reply_parameters`，并指定 `checklist_task_id`【736136284671642†L209-L215】。

示例：

```php
$bot->sendMessage($chatId, 'Task response', [
    'reply_parameters' => [
        'checklist_task_id' => 999,
    ],
]);
```

## 透传原则

SDK 不会预先定义这些参数，而是将你传入的数组直接作为请求体的一部分。数组中的嵌套结构会被自动转换为 JSON。例如，`reply_parameters` 是一个嵌套数组，最终会被序列化为 JSON 对象。这样可避免 SDK 在 Telegram 发布新参数时需要频繁更新。

更多有关这些参数的说明请参见 [Telegram Bot API 文档](https://core.telegram.org/bots/api#available-methods)。