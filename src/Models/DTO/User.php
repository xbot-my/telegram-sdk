<?php

declare(strict_types=1);

namespace XBot\Telegram\Models\DTO;

use XBot\Telegram\Exceptions\ValidationException;

/**
 * Telegram User DTO
 * 
 * 表示 Telegram 用户信息
 * 
 * @see https://core.telegram.org/bots/api#user
 */
class User extends BaseDTO
{
    /**
     * 用户的唯一标识符
     */
    public int $id;

    /**
     * 如果该用户是机器人则为 True
     */
    public bool $isBot;

    /**
     * 用户或机器人的名字
     */
    public string $firstName;

    /**
     * 用户或机器人的姓氏（可选）
     */
    public ?string $lastName = null;

    /**
     * 用户或机器人的用户名（可选）
     */
    public ?string $username = null;

    /**
     * IETF 语言标签中的用户语言（可选）
     */
    public ?string $languageCode = null;

    /**
     * 如果该用户是 Telegram Premium 用户则为 True（可选）
     */
    public ?bool $isPremium = null;

    /**
     * 如果该用户添加了机器人到附件菜单则为 True（可选）
     */
    public ?bool $addedToAttachmentMenu = null;

    /**
     * 如果机器人可以邀请到群组则为 True（可选）
     */
    public ?bool $canJoinGroups = null;

    /**
     * 如果启用了隐私模式则为 True（可选）
     */
    public ?bool $canReadAllGroupMessages = null;

    /**
     * 如果机器人支持内联查询则为 True（可选）
     */
    public ?bool $supportsInlineQueries = null;

    /**
     * 如果机器人可以连接到 Telegram 商业账户以接收其消息则为 True（可选）
     */
    public ?bool $canConnectToBusiness = null;

    /**
     * 验证用户数据
     */
    public function validate(): void
    {
        // 验证用户 ID
        if (!isset($this->id)) {
            throw ValidationException::required('id');
        }

        if (!is_int($this->id) || $this->id <= 0) {
            throw ValidationException::invalidType('id', 'positive integer', $this->id);
        }

        // 验证 isBot 字段
        if (!isset($this->isBot)) {
            throw ValidationException::required('isBot');
        }

        if (!is_bool($this->isBot)) {
            throw ValidationException::invalidType('isBot', 'boolean', $this->isBot);
        }

        // 验证 firstName 字段
        if (!isset($this->firstName)) {
            throw ValidationException::required('firstName');
        }

        if (!is_string($this->firstName) || trim($this->firstName) === '') {
            throw ValidationException::invalidType('firstName', 'non-empty string', $this->firstName);
        }

        // 验证 lastName 字段（可选）
        if ($this->lastName !== null && (!is_string($this->lastName) || trim($this->lastName) === '')) {
            throw ValidationException::invalidType('lastName', 'string or null', $this->lastName);
        }

        // 验证 username 字段（可选）
        if ($this->username !== null) {
            if (!is_string($this->username)) {
                throw ValidationException::invalidType('username', 'string or null', $this->username);
            }

            // 用户名应该以字母、数字或下划线开头，长度 5-32 个字符
            if (!preg_match('/^[a-zA-Z0-9_]{5,32}$/', $this->username)) {
                throw ValidationException::invalidFormat('username', 'valid Telegram username (5-32 chars, alphanumeric and underscore)', $this->username);
            }
        }

        // 验证 languageCode 字段（可选）
        if ($this->languageCode !== null) {
            if (!is_string($this->languageCode)) {
                throw ValidationException::invalidType('languageCode', 'string or null', $this->languageCode);
            }

            // 语言代码应该是 2-8 个字符的 IETF 语言标签
            if (!preg_match('/^[a-z]{2}(-[A-Z]{2})?$/', $this->languageCode)) {
                throw ValidationException::invalidFormat('languageCode', 'IETF language tag (e.g., en, en-US)', $this->languageCode);
            }
        }

        // 验证布尔类型的可选字段
        $booleanFields = ['isPremium', 'addedToAttachmentMenu', 'canJoinGroups', 'canReadAllGroupMessages', 'supportsInlineQueries', 'canConnectToBusiness'];
        
        foreach ($booleanFields as $field) {
            if ($this->$field !== null && !is_bool($this->$field)) {
                throw ValidationException::invalidType($field, 'boolean or null', $this->$field);
            }
        }
    }

    /**
     * 获取用户全名
     */
    public function getFullName(): string
    {
        $name = $this->firstName;
        
        if ($this->lastName) {
            $name .= ' ' . $this->lastName;
        }

        return $name;
    }

    /**
     * 获取用户显示名称（优先使用用户名，否则使用全名）
     */
    public function getDisplayName(): string
    {
        return $this->username ? '@' . $this->username : $this->getFullName();
    }

    /**
     * 获取用户提及格式
     */
    public function getMention(): string
    {
        if ($this->username) {
            return '@' . $this->username;
        }

        return "[{$this->getFullName()}](tg://user?id={$this->id})";
    }

    /**
     * 检查是否为机器人
     */
    public function isBot(): bool
    {
        return $this->isBot;
    }

    /**
     * 检查是否为真实用户
     */
    public function isHuman(): bool
    {
        return !$this->isBot;
    }

    /**
     * 检查是否为 Premium 用户
     */
    public function isPremium(): bool
    {
        return $this->isPremium === true;
    }

    /**
     * 检查是否有用户名
     */
    public function hasUsername(): bool
    {
        return $this->username !== null && $this->username !== '';
    }

    /**
     * 检查是否有姓氏
     */
    public function hasLastName(): bool
    {
        return $this->lastName !== null && $this->lastName !== '';
    }

    /**
     * 检查是否支持内联查询（仅对机器人有效）
     */
    public function supportsInlineQueries(): bool
    {
        return $this->isBot && $this->supportsInlineQueries === true;
    }

    /**
     * 检查是否可以加入群组（仅对机器人有效）
     */
    public function canJoinGroups(): bool
    {
        return $this->isBot && $this->canJoinGroups === true;
    }

    /**
     * 检查是否可以读取所有群组消息（仅对机器人有效）
     */
    public function canReadAllGroupMessages(): bool
    {
        return $this->isBot && $this->canReadAllGroupMessages === true;
    }

    /**
     * 获取用户资料链接
     */
    public function getProfileUrl(): string
    {
        if ($this->username) {
            return "https://t.me/{$this->username}";
        }

        return "tg://user?id={$this->id}";
    }

    /**
     * 比较两个用户是否相同
     */
    public function equals(?User $other): bool
    {
        return $other !== null && $this->id === $other->id;
    }

    /**
     * 创建机器人用户实例
     */
    public static function createBot(
        int $id,
        string $firstName,
        ?string $username = null,
        ?string $lastName = null,
        bool $canJoinGroups = false,
        bool $canReadAllGroupMessages = false,
        bool $supportsInlineQueries = false,
        bool $canConnectToBusiness = false
    ): static {
        return static::fromArray([
            'id' => $id,
            'is_bot' => true,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'username' => $username,
            'can_join_groups' => $canJoinGroups,
            'can_read_all_group_messages' => $canReadAllGroupMessages,
            'supports_inline_queries' => $supportsInlineQueries,
            'can_connect_to_business' => $canConnectToBusiness,
        ]);
    }

    /**
     * 创建真实用户实例
     */
    public static function createUser(
        int $id,
        string $firstName,
        ?string $lastName = null,
        ?string $username = null,
        ?string $languageCode = null,
        bool $isPremium = false
    ): static {
        return static::fromArray([
            'id' => $id,
            'is_bot' => false,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'username' => $username,
            'language_code' => $languageCode,
            'is_premium' => $isPremium,
        ]);
    }
}