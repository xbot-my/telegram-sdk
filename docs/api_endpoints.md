# API Endpoints Overview (Basics)

## Webhook Basics
- setWebhook(url, options): Registers a webhook URL.
  - Required: https URL.
  - Common options: `secret_token`, `max_connections`, `allowed_updates`, `certificate` (local file path triggers upload).
- deleteWebhook(dropPendingUpdates=false): Removes the webhook.
- getWebhookInfo(): Returns current webhook status and stats.

Example
```
$bot->setWebhook('https://your.app/telegram/webhook/main', [
  'secret_token' => 'your-secret',
  'allowed_updates' => ['message','callback_query'],
]);
$info = $bot->getWebhookInfo();
$bot->deleteWebhook(true);
```

## Messaging Basics
- sendMessage(chatId, text, options): Sends a text message.
  - Common options: `parse_mode` ('HTML'|'Markdown'), `disable_notification`,
    `reply_markup` (inline/reply keyboard), `reply_to_message_id`.
- editMessageText(chatId, messageId, text, options): Edits a message.
- editMessageCaption(chatId, messageId, caption, options): Edits media caption.
- editMessageReplyMarkup(chatId, messageId, replyMarkup): Updates inline/reply keyboard only.
- deleteMessage(chatId, messageId): Deletes a message.
- forwardMessage(chatId, fromChatId, messageId, options): Forwards a message.
- copyMessage(chatId, fromChatId, messageId, options): Copies a message.

### Keyboards
- Inline: pass `reply_markup: { inline_keyboard: [[{ text, callback_data }]] }` or use `InlineKeyboardBuilder`.
- Reply: pass `reply_markup: { keyboard: [[{ text }]], resize_keyboard, one_time_keyboard }` or use `ReplyKeyboardBuilder`.
- Remove: `reply_markup: { remove_keyboard: true }`.
- Force reply: `reply_markup: { force_reply: true, input_field_placeholder }`.

Builders
```
use XBot\\Telegram\\Keyboard\\InlineKeyboardBuilder as IK;
use XBot\\Telegram\\Keyboard\\ReplyKeyboardBuilder as RK;

$ik = IK::make()->row(IK::button('OK', ['callback_data' => 'ok']));
$rk = RK::make()->row(RK::button('Yes'), RK::button('No'))->resize()->oneTime();

$bot->sendMessage(123, 'Inline', ['reply_markup' => $ik->toArray()]);
$bot->sendMessage(123, 'Reply', ['reply_markup' => $rk->toArray()]);
```

Fluent Entry (framework-agnostic)
```
use XBot\Telegram\Bot;
Bot::init(['default'=>'main','bots'=>['main'=>['token'=>'TOKEN']]]);
Bot::to(123456)->html()->message('<b>Hello</b>');
```

Notes
- Local file paths automatically go through upload for media endpoints.
- Validation: chat IDs, message IDs, coordinates, and webhook URLs are validated before request.

## Files & Profiles
- getFile(fileId): Returns file info including `file_path`.
- getUserProfilePhotos(userId, options): Returns profile photos (`limit`, `offset`).

## Chat/Admin Basics
- getChatAdministrators(chatId): List admins of a chat.
- setChatTitle(chatId, title) / setChatDescription(chatId, desc): Update chat metadata.
- setChatPhoto(chatId, photo) / deleteChatPhoto(chatId): Set/remove photo (local path triggers upload).
- pinChatMessage(chatId, messageId, disableNotification=false) / unpinChatMessage(chatId, messageId=null) / unpinAllChatMessages(chatId): Pin controls.
- leaveChat(chatId): Bot leaves the chat.
## Commands
- setMyCommands(commands, options)
- getMyCommands(options)
- deleteMyCommands(options)

Example
```
$bot->setMyCommands([
  ['command' => 'start', 'description' => 'Start the bot'],
  ['command' => 'help', 'description' => 'Help menu'],
], [ 'scope' => ['type' => 'default'], 'language_code' => '' ]);
```
## Inline Queries
- Build results via helpers or arrays, then call `answerInlineQuery(id, results, options)`.

Example
```
use XBot\\Telegram\\Inline\\InlineResultBuilder as IR;

$results = [
  IR::article('1', 'Hello', IR::markdown('*Hi*')),
  IR::photo('2', 'https://example.com/p.jpg', 'https://example.com/t.jpg', ['caption' => 'cap']),
];

$bot->answerInlineQuery('INLINE_QUERY_ID', $results, ['cache_time' => 0]);
```
