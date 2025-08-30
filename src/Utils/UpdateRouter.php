<?php

declare(strict_types=1);

namespace XBot\Telegram\Utils;

final class UpdateRouter
{
    public const TYPES = [
        'message',
        'edited_message',
        'channel_post',
        'edited_channel_post',
        'inline_query',
        'chosen_inline_result',
        'callback_query',
        'shipping_query',
        'pre_checkout_query',
        'poll',
        'poll_answer',
        'my_chat_member',
        'chat_member',
        'chat_join_request',
    ];

    public static function detectType(array $update): ?string
    {
        foreach (self::TYPES as $t) {
            if (array_key_exists($t, $update)) {
                return $t;
            }
        }
        return null;
    }

    public static function studly(string $type): string
    {
        $type = str_replace('_', ' ', $type);
        $type = ucwords($type);
        return str_replace(' ', '', $type);
    }

    public static function chatId(array $update): int|string|null
    {
        $message = $update['message']
            ?? $update['edited_message']
            ?? $update['channel_post']
            ?? $update['edited_channel_post']
            ?? ($update['callback_query']['message'] ?? null)
            ?? null;
        return is_array($message) ? ($message['chat']['id'] ?? null) : null;
    }

    public static function userId(array $update): ?int
    {
        $user = $update['message']['from']
            ?? $update['edited_message']['from']
            ?? $update['callback_query']['from']
            ?? $update['inline_query']['from']
            ?? $update['poll_answer']['user']
            ?? $update['chat_member']['from']
            ?? $update['my_chat_member']['from']
            ?? null;
        return is_array($user) ? (int) ($user['id'] ?? 0) ?: null : null;
    }

    public static function text(array $update): ?string
    {
        if (isset($update['message']['text'])) {
            return (string) $update['message']['text'];
        }
        if (isset($update['callback_query']['data'])) {
            return (string) $update['callback_query']['data'];
        }
        return null;
    }
}

