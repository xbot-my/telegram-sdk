<?php

declare(strict_types=1);

namespace XBot\Telegram\Keyboard;

final class ReplyKeyboardRemove
{
    public static function make(bool $selective = false): array
    {
        $out = ['remove_keyboard' => true];
        if ($selective) $out['selective'] = true;
        return $out;
    }
}

