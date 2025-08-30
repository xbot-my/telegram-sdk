<?php

declare(strict_types=1);

namespace XBot\Telegram\Utils;

final class CommandParser
{
    /**
     * Parse a Telegram command from text.
     * Examples: "/start", "/help arg1 arg2", "/echo@MyBot hi".
     * Returns [command => 'start', args => ['arg1','arg2'], mention => 'MyBot'] or null.
     */
    public static function parse(?string $text): ?array
    {
        if ($text === null) {
            return null;
        }
        $text = trim($text);
        if ($text === '' || $text[0] !== '/') {
            return null;
        }
        // Extract first token (command + optional @mention)
        $parts = preg_split('/\s+/', $text, 2) ?: [];
        $head = $parts[0] ?? '';
        $tail = $parts[1] ?? '';

        // Remove leading '/'
        $head = substr($head, 1);
        // Split command@mention
        $cmdParts = explode('@', $head, 2);
        $command = strtolower($cmdParts[0] ?? '');
        $mention = $cmdParts[1] ?? null;
        if ($command === '') {
            return null;
        }
        $args = $tail === '' ? [] : preg_split('/\s+/', trim($tail)) ?: [];
        return [
            'command' => $command,
            'mention' => $mention,
            'args'    => array_values(array_filter($args, static fn($a) => $a !== '')),
        ];
    }
}

