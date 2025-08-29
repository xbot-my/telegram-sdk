<?php
declare(strict_types=1);

namespace XBot\Telegram\Contracts;

/**
 * Minimal contract for DTOs that can be constructed from arrays.
 */
interface FromArray
{
    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): static;
}

