<?php

declare(strict_types=1);

namespace XBot\Telegram\Keyboard;

final class ReplyButton
{
    public static function text(string $text): array
    {
        return ['text' => $text];
    }

    public static function contact(string $text): array
    {
        return ['text' => $text, 'request_contact' => true];
    }

    public static function location(string $text): array
    {
        return ['text' => $text, 'request_location' => true];
    }

    public static function poll(string $text, string $type = 'regular'): array
    {
        return ['text' => $text, 'request_poll' => ['type' => $type]];
    }
}

