<?php

declare(strict_types=1);

namespace XBot\Telegram\Models\DTO;

use XBot\Telegram\Exceptions\ValidationException;

/**
 * Telegram Chat DTO
 * 
 * 表示聊天信息
 * 
 * @see https://core.telegram.org/bots/api#chat
 */
class Chat extends BaseDTO
{
    /**
     * 聊天类型常量
     */
    public const TYPE_PRIVATE = 'private';
    public const TYPE_GROUP = 'group';
    public const TYPE_SUPERGROUP = 'supergroup';
    public const TYPE_CHANNEL = 'channel';

    /**
     * 聊天的唯一标识符
     */
    public int $id;

    /**
     * 聊天类型，可以是 "private", "group", "supergroup" 或 "channel"
     */
    public string $type;

    /**
     * 聊天标题，对于频道和群组
     */
    public ?string $title = null;

    /**
     * 用户名，对于私人聊天、超级群组和频道（如果可用）
     */
    public ?string $username = null;

    /**
     * 名字，对于私人聊天
     */
    public ?string $firstName = null;

    /**
     * 姓氏，对于私人聊天
     */
    public ?string $lastName = null;

    /**
     * 如果聊天是论坛则为 True（仅超级群组）
     */
    public ?bool $isForum = null;

    /**
     * 聊天照片
     */
    public ?ChatPhoto $photo = null;

    /**
     * 如果非空，聊天中所有用户的主要邀请链接列表
     */
    public ?array $activeUsernames = null;

    /**
     * 对于私人聊天，当前用户可用的表情符号状态列表
     */
    public ?array $availableReactions = null;

    /**
     * 表情符号状态有效期到期的时间戳
     */
    public ?int $accentColorId = null;

    /**
     * 背景自定义表情符号标识符
     */
    public ?string $backgroundCustomEmojiId = null;

    /**
     * 个人资料强调色标识符
     */
    public ?int $profileAccentColorId = null;

    /**
     * 个人资料背景自定义表情符号标识符
     */
    public ?string $profileBackgroundCustomEmojiId = null;

    /**
     * 表情符号状态
     */
    public ?string $emojiStatusCustomEmojiId = null;

    /**
     * 表情符号状态到期日期
     */
    public ?int $emojiStatusExpirationDate = null;

    /**
     * 聊天生物/描述
     */
    public ?string $bio = null;

    /**
     * 如果该聊天具有受保护的内容则为 True
     */
    public ?bool $hasProtectedContent = null;

    /**
     * 如果该聊天中的消息不能转发到其他聊天则为 True
     */
    public ?bool $hasRestrictedVoiceAndVideoMessages = null;

    /**
     * 如果用户需要加入超级群组才能发送消息则为 True
     */
    public ?bool $joinToSendMessages = null;

    /**
     * 如果用户需要加入超级群组才能发送媒体消息则为 True
     */
    public ?bool $joinByRequest = null;

    /**
     * 聊天描述
     */
    public ?string $description = null;

    /**
     * 聊天的主要邀请链接
     */
    public ?string $inviteLink = null;

    /**
     * 聊天中已固定的最新消息
     */
    public ?Message $pinnedMessage = null;

    /**
     * 聊天的默认权限
     */
    public ?ChatPermissions $permissions = null;

    /**
     * 对于超级群组，慢速模式延迟的名称，用户被限制连续发送消息
     */
    public ?int $slowModeDelay = null;

    /**
     * 消息在超级群组中自动删除的时间
     */
    public ?int $messageAutoDeleteTime = null;

    /**
     * 如果消息发送者的签名在频道中应该显示则为 True
     */
    public ?bool $hasAggressiveAntiSpamEnabled = null;

    /**
     * 如果该聊天是隐藏成员的超级群组则为 True
     */
    public ?bool $hasHiddenMembers = null;

    /**
     * 用于创建聊天文件夹邀请链接的自定义表情符号标识符
     */
    public ?string $customEmojiStickerSetName = null;

    /**
     * 聊天的位置信息，对于超级群组和频道
     */
    public ?ChatLocation $location = null;

    /**
     * 验证聊天数据
     */
    public function validate(): void
    {
        // 验证聊天 ID
        if (!isset($this->id)) {
            throw ValidationException::required('id');
        }

        if (!is_int($this->id)) {
            throw ValidationException::invalidType('id', 'integer', $this->id);
        }

        // 验证聊天类型
        if (!isset($this->type)) {
            throw ValidationException::required('type');
        }

        $validTypes = [self::TYPE_PRIVATE, self::TYPE_GROUP, self::TYPE_SUPERGROUP, self::TYPE_CHANNEL];
        if (!in_array($this->type, $validTypes, true)) {
            throw ValidationException::invalidEnum('type', $validTypes, $this->type);
        }

        // 根据聊天类型验证必填字段
        $this->validateByType();

        // 验证用户名格式（如果存在）
        if ($this->username !== null) {
            if (!is_string($this->username) || !preg_match('/^[a-zA-Z0-9_]{5,32}$/', $this->username)) {
                throw ValidationException::invalidFormat('username', 'valid Telegram username', $this->username);
            }
        }

        // 验证布尔字段
        $booleanFields = [
            'isForum', 'hasProtectedContent', 'hasRestrictedVoiceAndVideoMessages',
            'joinToSendMessages', 'joinByRequest', 'hasAggressiveAntiSpamEnabled', 'hasHiddenMembers'
        ];

        foreach ($booleanFields as $field) {
            if ($this->$field !== null && !is_bool($this->$field)) {
                throw ValidationException::invalidType($field, 'boolean or null', $this->$field);
            }
        }

        // 验证时间戳字段
        $timestampFields = ['emojiStatusExpirationDate'];
        foreach ($timestampFields as $field) {
            if ($this->$field !== null && (!is_int($this->$field) || $this->$field < 0)) {
                throw ValidationException::invalidType($field, 'positive integer or null', $this->$field);
            }
        }

        // 验证延迟字段
        if ($this->slowModeDelay !== null && (!is_int($this->slowModeDelay) || $this->slowModeDelay < 0 || $this->slowModeDelay > 21600)) {
            throw ValidationException::invalidRange('slowModeDelay', 0, 21600, $this->slowModeDelay);
        }

        if ($this->messageAutoDeleteTime !== null && (!is_int($this->messageAutoDeleteTime) || $this->messageAutoDeleteTime < 0)) {
            throw ValidationException::invalidType('messageAutoDeleteTime', 'positive integer or null', $this->messageAutoDeleteTime);
        }
    }

    /**
     * 根据聊天类型验证必填字段
     */
    protected function validateByType(): void
    {
        switch ($this->type) {
            case self::TYPE_PRIVATE:
                // 私人聊天必须有 firstName
                if (!isset($this->firstName) || !is_string($this->firstName) || trim($this->firstName) === '') {
                    throw ValidationException::required('firstName for private chat');
                }
                break;

            case self::TYPE_GROUP:
            case self::TYPE_SUPERGROUP:
            case self::TYPE_CHANNEL:
                // 群组和频道必须有 title
                if (!isset($this->title) || !is_string($this->title) || trim($this->title) === '') {
                    throw ValidationException::required('title for group/supergroup/channel');
                }
                break;
        }
    }

    /**
     * 检查是否为私人聊天
     */
    public function isPrivate(): bool
    {
        return $this->type === self::TYPE_PRIVATE;
    }

    /**
     * 检查是否为群组
     */
    public function isGroup(): bool
    {
        return $this->type === self::TYPE_GROUP;
    }

    /**
     * 检查是否为超级群组
     */
    public function isSupergroup(): bool
    {
        return $this->type === self::TYPE_SUPERGROUP;
    }

    /**
     * 检查是否为频道
     */
    public function isChannel(): bool
    {
        return $this->type === self::TYPE_CHANNEL;
    }

    /**
     * 检查是否为群组聊天（群组或超级群组）
     */
    public function isGroupChat(): bool
    {
        return $this->isGroup() || $this->isSupergroup();
    }

    /**
     * 检查是否为论坛
     */
    public function isForum(): bool
    {
        return $this->isForum === true;
    }

    /**
     * 获取聊天显示名称
     */
    public function getDisplayName(): string
    {
        if ($this->isPrivate()) {
            $name = $this->firstName ?? '';
            if ($this->lastName) {
                $name .= ' ' . $this->lastName;
            }
            return $name;
        }

        return $this->title ?? '';
    }

    /**
     * 获取聊天提及格式
     */
    public function getMention(): string
    {
        if ($this->username) {
            return '@' . $this->username;
        }

        if ($this->isPrivate()) {
            $name = $this->getDisplayName();
            return "[{$name}](tg://user?id={$this->id})";
        }

        return $this->getDisplayName();
    }

    /**
     * 获取聊天链接
     */
    public function getChatUrl(): ?string
    {
        if ($this->username) {
            return "https://t.me/{$this->username}";
        }

        if ($this->inviteLink) {
            return $this->inviteLink;
        }

        return null;
    }

    /**
     * 检查是否有受保护的内容
     */
    public function hasProtectedContent(): bool
    {
        return $this->hasProtectedContent === true;
    }

    /**
     * 检查是否有语音和视频消息限制
     */
    public function hasRestrictedVoiceAndVideoMessages(): bool
    {
        return $this->hasRestrictedVoiceAndVideoMessages === true;
    }

    /**
     * 检查是否需要加入才能发送消息
     */
    public function requiresJoinToSendMessages(): bool
    {
        return $this->joinToSendMessages === true;
    }

    /**
     * 检查是否需要请求加入
     */
    public function requiresJoinRequest(): bool
    {
        return $this->joinByRequest === true;
    }

    /**
     * 检查是否启用了积极的反垃圾邮件
     */
    public function hasAggressiveAntiSpamEnabled(): bool
    {
        return $this->hasAggressiveAntiSpamEnabled === true;
    }

    /**
     * 检查是否隐藏成员
     */
    public function hasHiddenMembers(): bool
    {
        return $this->hasHiddenMembers === true;
    }

    /**
     * 获取全名（私人聊天）
     */
    public function getFullName(): string
    {
        if (!$this->isPrivate()) {
            return $this->getDisplayName();
        }

        $name = $this->firstName ?? '';
        if ($this->lastName) {
            $name .= ' ' . $this->lastName;
        }

        return $name;
    }

    /**
     * 比较两个聊天是否相同
     */
    public function equals(?Chat $other): bool
    {
        return $other !== null && $this->id === $other->id;
    }
}

/**
 * 聊天照片 DTO
 */
class ChatPhoto extends BaseDTO
{
    /**
     * 小头像的文件标识符
     */
    public string $smallFileId;

    /**
     * 小头像的唯一文件标识符
     */
    public string $smallFileUniqueId;

    /**
     * 大头像的文件标识符
     */
    public string $bigFileId;

    /**
     * 大头像的唯一文件标识符
     */
    public string $bigFileUniqueId;

    public function validate(): void
    {
        $requiredFields = ['smallFileId', 'smallFileUniqueId', 'bigFileId', 'bigFileUniqueId'];
        
        foreach ($requiredFields as $field) {
            if (!isset($this->$field) || !is_string($this->$field) || trim($this->$field) === '') {
                throw ValidationException::required($field);
            }
        }
    }
}

/**
 * 聊天权限 DTO
 */
class ChatPermissions extends BaseDTO
{
    public ?bool $canSendMessages = null;
    public ?bool $canSendAudios = null;
    public ?bool $canSendDocuments = null;
    public ?bool $canSendPhotos = null;
    public ?bool $canSendVideos = null;
    public ?bool $canSendVideoNotes = null;
    public ?bool $canSendVoiceNotes = null;
    public ?bool $canSendPolls = null;
    public ?bool $canSendOtherMessages = null;
    public ?bool $canAddWebPagePreviews = null;
    public ?bool $canChangeInfo = null;
    public ?bool $canInviteUsers = null;
    public ?bool $canPinMessages = null;
    public ?bool $canManageTopics = null;

    public function validate(): void
    {
        $booleanFields = [
            'canSendMessages', 'canSendAudios', 'canSendDocuments', 'canSendPhotos',
            'canSendVideos', 'canSendVideoNotes', 'canSendVoiceNotes', 'canSendPolls',
            'canSendOtherMessages', 'canAddWebPagePreviews', 'canChangeInfo',
            'canInviteUsers', 'canPinMessages', 'canManageTopics'
        ];

        foreach ($booleanFields as $field) {
            if ($this->$field !== null && !is_bool($this->$field)) {
                throw ValidationException::invalidType($field, 'boolean or null', $this->$field);
            }
        }
    }
}

/**
 * 聊天位置 DTO
 */
class ChatLocation extends BaseDTO
{
    /**
     * 位置信息
     */
    public Location $location;

    /**
     * 地址
     */
    public string $address;

    public function validate(): void
    {
        if (!isset($this->location)) {
            throw ValidationException::required('location');
        }

        if (!isset($this->address) || !is_string($this->address) || trim($this->address) === '') {
            throw ValidationException::required('address');
        }

        if ($this->location instanceof Location) {
            $this->location->validate();
        }
    }
}

/**
 * 位置 DTO
 */
class Location extends BaseDTO
{
    /**
     * 纬度
     */
    public float $latitude;

    /**
     * 经度
     */
    public float $longitude;

    /**
     * 位置不确定性半径，单位为米；0-1500
     */
    public ?float $horizontalAccuracy = null;

    /**
     * 实时位置更新的时间（以秒为单位），应该在 60 到 86400 之间
     */
    public ?int $livePeriod = null;

    /**
     * 实时位置朝向的方向，单位为度；1-360
     */
    public ?int $heading = null;

    /**
     * 接近警报的最大距离，单位为米（1-100000）
     */
    public ?int $proximityAlertRadius = null;

    public function validate(): void
    {
        if (!isset($this->latitude)) {
            throw ValidationException::required('latitude');
        }

        if (!isset($this->longitude)) {
            throw ValidationException::required('longitude');
        }

        if (!is_float($this->latitude) && !is_int($this->latitude)) {
            throw ValidationException::invalidType('latitude', 'float', $this->latitude);
        }

        if (!is_float($this->longitude) && !is_int($this->longitude)) {
            throw ValidationException::invalidType('longitude', 'float', $this->longitude);
        }

        // 验证纬度范围
        if ($this->latitude < -90 || $this->latitude > 90) {
            throw ValidationException::invalidRange('latitude', -90, 90, $this->latitude);
        }

        // 验证经度范围
        if ($this->longitude < -180 || $this->longitude > 180) {
            throw ValidationException::invalidRange('longitude', -180, 180, $this->longitude);
        }

        // 验证可选字段
        if ($this->horizontalAccuracy !== null && ($this->horizontalAccuracy < 0 || $this->horizontalAccuracy > 1500)) {
            throw ValidationException::invalidRange('horizontalAccuracy', 0, 1500, $this->horizontalAccuracy);
        }

        if ($this->livePeriod !== null && ($this->livePeriod < 60 || $this->livePeriod > 86400)) {
            throw ValidationException::invalidRange('livePeriod', 60, 86400, $this->livePeriod);
        }

        if ($this->heading !== null && ($this->heading < 1 || $this->heading > 360)) {
            throw ValidationException::invalidRange('heading', 1, 360, $this->heading);
        }

        if ($this->proximityAlertRadius !== null && ($this->proximityAlertRadius < 1 || $this->proximityAlertRadius > 100000)) {
            throw ValidationException::invalidRange('proximityAlertRadius', 1, 100000, $this->proximityAlertRadius);
        }
    }
}