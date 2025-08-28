# API Endpoints Overview (Basics)

## Webhook Basics
- setWebhook(url, options): Registers a webhook URL.
  - Required: https URL.
  - Common options: `secret_token`, `max_connections`, `allowed_updates`.
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
- deleteMessage(chatId, messageId): Deletes a message.
- forwardMessage(chatId, fromChatId, messageId, options): Forwards a message.
- copyMessage(chatId, fromChatId, messageId, options): Copies a message.

Fluent Entry (framework-agnostic)
```
use XBot\Telegram\Bot;
Bot::init(['default'=>'main','bots'=>['main'=>['token'=>'TOKEN']]]);
Bot::to(123456)->html()->message('<b>Hello</b>');
```

Notes
- Local file paths automatically go through upload for media endpoints.
- Validation: chat IDs, message IDs, coordinates, and webhook URLs are validated before request.
