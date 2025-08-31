<?php

declare(strict_types=1);

namespace XBot\Telegram\Http\Response;

final class Transformer
{
    /**
     * @var mixed
     */
    private mixed $data;

    public function __construct(mixed $data)
    {
        $this->data = $data;
    }

    /**
     * Get raw payload without conversion.
     */
    public function raw(): mixed
    {
        return $this->data;
    }

    /**
     * Convert result to array (no-op when already array).
     */
    public function toArray(): array
    {
        return is_array($this->data) ? $this->data : (array)$this->data;
    }

    /**
     * Convert associative arrays recursively to stdClass object(s).
     */
    public function toObject(): object|array|string|int|float|bool|null
    {
        return self::arrayToObject($this->data);
    }

    /**
     * Convert result to JSON string.
     */
    public function toJson(int $flags = JSON_UNESCAPED_UNICODE): string
    {
        return json_encode($this->data, $flags) ?: 'null';
    }

    /**
     * Convert result to Illuminate Collection.
     * @throws \RuntimeException when illuminate/support is not available.
     */
    public function collection(): \Illuminate\Support\Collection
    {
        if (!class_exists('Illuminate\\Support\\Collection')) {
            throw new \RuntimeException('Collection format requires illuminate/support.');
        }

        return new \Illuminate\Support\Collection($this->data);
    }

    private static function arrayToObject(mixed $data): mixed
    {
        if (is_array($data)) {
            if (array_is_list($data)) {
                return array_map([self::class, 'arrayToObject'], $data);
            }

            $obj = new \stdClass();
            foreach ($data as $k => $v) {
                $obj->{$k} = self::arrayToObject($v);
            }

            return $obj;
        }

        return $data;
    }
}
