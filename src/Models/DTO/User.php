<?php
declare(strict_types=1);

namespace XBot\Telegram\Models\DTO;

/**
 * Minimal User DTO. Fields are accessed fluently, e.g. $user->id, $user->username.
 */
class User extends Dto
{
    /** Convenience detector: is this bot account? (Telegram field: is_bot) */
    public function isBot(): bool
    {
        return (bool) ($this->is_bot ?? false);
    }
}

