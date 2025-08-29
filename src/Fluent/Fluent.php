<?php
declare(strict_types=1);

namespace XBot\Telegram\Fluent;

use ArrayAccess;
use Countable;
use IteratorAggregate;
use JsonSerializable;
use Traversable;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;

/**
 * Lightweight array-backed object with Laravel-friendly interfaces.
 * - Recursive wrapping of nested arrays
 * - Magic access (__get) and detectors (__call: isX())
 * - Implements Arrayable/Jsonable and common PHP interfaces
 */
class Fluent implements ArrayAccess, IteratorAggregate, Countable, JsonSerializable, Arrayable, Jsonable
{
    /** @var array<string, mixed> */
    protected array $attributes;

    /**
     * @param array<string, mixed> $attributes
     */
    public function __construct(array $attributes = [])
    {
        $this->attributes = $this->wrapNested($attributes);
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): static
    {
        return new static($data);
    }

    /**
     * Access attribute via object property. Unknown keys return null.
     */
    public function __get(string $name): mixed
    {
        $key = $this->keyFromMagic($name);
        return $this->attributes[$key] ?? null;
    }

    public function __isset(string $name): bool
    {
        $key = $this->keyFromMagic($name);
        return array_key_exists($key, $this->attributes);
    }

    /**
     * Magic call for detectors and simple getters.
     * - isX() -> checks existence/truthiness of key `x` (snake_case)
     * - x()   -> returns value of key `x` (snake_case)
     */
    public function __call(string $name, array $arguments): mixed
    {
        if (str_starts_with($name, 'is') && strlen($name) > 2) {
            $key = $this->snake(substr($name, 2));
            $value = $this->attributes[$key] ?? null;
            return (bool) $value;
        }

        $key = $this->snake($name);
        return $this->attributes[$key] ?? null;
    }

    /**
     * Get value by exact key (no snake/camel transform).
     */
    public function get(string $key, mixed $default = null): mixed
    {
        return $this->attributes[$key] ?? $default;
    }

    /**
     * Map current item into a DTO implementing Arrayable via provided class::fromArray.
     * @template T of object
     * @param class-string<T> $class
     * @return T
     */
    public function as(string $class): object
    {
        if (!method_exists($class, 'fromArray')) {
            throw new \InvalidArgumentException("{$class} must define static fromArray(array): static");
        }
        return $class::fromArray($this->toArray());
    }

    /**
     * Convert to PHP array (recursively unwraps nested Fluent instances).
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $out = [];
        foreach ($this->attributes as $k => $v) {
            if ($v instanceof self) {
                $out[$k] = $v->toArray();
            } elseif ($v instanceof Arrayable) {
                $out[$k] = $v->toArray();
            } elseif ($v instanceof JsonSerializable) {
                $out[$k] = $v->jsonSerialize();
            } else {
                $out[$k] = $v;
            }
        }
        return $out;
    }

    public function toJson($options = 0): string
    {
        return json_encode($this->toArray(), JSON_THROW_ON_ERROR | $options);
    }

    public function jsonSerialize(): mixed
    {
        return $this->toArray();
    }

    public function getIterator(): Traversable
    {
        return new \ArrayIterator($this->attributes);
    }

    public function count(): int
    {
        return count($this->attributes);
    }

    public function offsetExists(mixed $offset): bool
    {
        return isset($this->attributes[(string) $offset]);
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->attributes[(string) $offset] ?? null;
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->attributes[(string) $offset] = $this->wrapValue($value);
    }

    public function offsetUnset(mixed $offset): void
    {
        unset($this->attributes[(string) $offset]);
    }

    /**
     * Returns a Laravel Collection when available.
     */
    public function collect(): object
    {
        if (class_exists('Illuminate\\Support\\Collection')) {
            /** @var class-string $collection */
            $collection = 'Illuminate\\Support\\Collection';
            return new $collection($this->toArray());
        }
        // Fallback: ArrayObject for non-Laravel context
        return new \ArrayObject($this->toArray());
    }

    /** @param array<string, mixed> $data */
    protected function wrapNested(array $data): array
    {
        foreach ($data as $k => $v) {
            $data[$k] = $this->wrapValue($v);
        }
        return $data;
    }

    protected function wrapValue(mixed $value): mixed
    {
        if (is_array($value)) {
            return new static($value);
        }
        return $value;
    }

    protected function keyFromMagic(string $name): string
    {
        // Allow direct key if present; otherwise camel->snake
        if (array_key_exists($name, $this->attributes)) {
            return $name;
        }
        return $this->snake($name);
    }

    protected function snake(string $name): string
    {
        $name = preg_replace('/[A-Z]/', '_$0', $name) ?? $name;
        $name = strtolower(trim($name, '_'));
        return $name;
    }
}

