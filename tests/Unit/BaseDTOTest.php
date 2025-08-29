<?php

declare(strict_types=1);

use XBot\Telegram\Models\DTO\BaseDTO;

class SampleNestedDTO extends BaseDTO
{
    public ?string $name = null;
}

class SampleDTO extends BaseDTO
{
    public int $age;
    public float $ratio;
    public bool $ok;
    public array $tags;
    public ?\DateTime $when = null;
    public ?SampleNestedDTO $child = null;
}

it('fills and serializes DTOs with type conversion', function () {
    $dto = SampleDTO::fromArray([
        'age' => '42', // string to int
        'ratio' => '3.14', // string to float
        'ok' => 1, // int to bool
        'tags' => 'one', // scalar to array
        'when' => time(), // timestamp to DateTime
        'child' => ['name' => 'bob'], // nested DTO
    ]);

    expect($dto->age)->toBe(42)
        ->and($dto->ratio)->toBeFloat()
        ->and($dto->ok)->toBeTrue()
        ->and($dto->tags)->toBeArray()
        ->and($dto->when)->toBeInstanceOf(DateTime::class)
        ->and($dto->child)->toBeInstanceOf(SampleNestedDTO::class)
        ->and($dto->child->name)->toBe('bob');

    $arr = $dto->toArray();
    expect($arr)->toHaveKeys(['age', 'ratio', 'ok', 'tags', 'when', 'child'])
        ->and(is_int($arr['when']))->toBeTrue()
        ->and($arr['child'])->toMatchArray(['name' => 'bob']);
});

