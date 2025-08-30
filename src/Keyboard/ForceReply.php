<?php

declare(strict_types=1);

namespace XBot\Telegram\Keyboard;

final class ForceReply
{
    public static function make(bool $selective = false, ?string $placeholder = null): array
    {
        $out = ['force_reply' => true];
        if ($selective) $out['selective'] = true;
        if ($placeholder !== null) $out['input_field_placeholder'] = $placeholder;
        return $out;
    }
}

