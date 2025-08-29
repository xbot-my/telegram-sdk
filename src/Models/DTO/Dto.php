<?php
declare(strict_types=1);

namespace XBot\Telegram\Models\DTO;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use JsonSerializable;
use XBot\Telegram\Contracts\FromArray;
use XBot\Telegram\Fluent\Fluent;

/**
 * Base DTO built on top of Fluent for minimal boilerplate.
 */
abstract class Dto extends Fluent implements FromArray, Arrayable, Jsonable, JsonSerializable
{
    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): static
    {
        return new static($data);
    }
}

