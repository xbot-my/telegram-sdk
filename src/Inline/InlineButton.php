<?php

declare(strict_types=1);

namespace XBot\Telegram\Inline;

final class InlineButton
{
    public static function url(string $text, string $url): array
    {
        return ['text' => $text, 'url' => $url];
    }

    public static function callback(string $text, string $data): array
    {
        return ['text' => $text, 'callback_data' => $data];
    }

    public static function switchInline(string $text, string $query = ''): array
    {
        return ['text' => $text, 'switch_inline_query' => $query];
    }

    public static function switchInlineCurrent(string $text, string $query = ''): array
    {
        return ['text' => $text, 'switch_inline_query_current_chat' => $query];
    }
}

