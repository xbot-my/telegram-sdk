<?php

declare(strict_types=1);

namespace XBot\Telegram\Models\DTO;

use XBot\Telegram\Contracts\DTOInterface;

/**
 * 联系人对象
 * 
 * 表示电话联系人的数据传输对象
 */
class Contact extends BaseDTO implements DTOInterface
{
    public readonly string $phoneNumber;
    public readonly string $firstName;
    public readonly ?string $lastName;
    public readonly ?int $userId;
    public readonly ?string $vcard;

    public function __construct(
        string $phoneNumber,
        string $firstName,
        ?string $lastName = null,
        ?int $userId = null,
        ?string $vcard = null
    ) {
        $this->phoneNumber = $phoneNumber;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->userId = $userId;
        $this->vcard = $vcard;

        parent::__construct();
    }

    public static function fromArray(array $data): static
    {
        return new static(
            phoneNumber: $data['phone_number'] ?? '',
            firstName: $data['first_name'] ?? '',
            lastName: $data['last_name'] ?? null,
            userId: isset($data['user_id']) ? (int) $data['user_id'] : null,
            vcard: $data['vcard'] ?? null
        );
    }

    public function validate(): void
    {
        if (empty($this->phoneNumber)) {
            throw new \InvalidArgumentException('Phone number is required');
        }
        if (empty($this->firstName)) {
            throw new \InvalidArgumentException('First name is required');
        }
    }

    public function getFullName(): string
    {
        return trim($this->firstName . ' ' . ($this->lastName ?? ''));
    }

    public function hasUserId(): bool
    {
        return $this->userId !== null;
    }
}