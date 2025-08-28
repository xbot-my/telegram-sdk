<?php

declare(strict_types=1);

namespace XBot\Telegram\Models\DTO;

use DateTime;
use XBot\Telegram\Exceptions\ValidationException;

/**
 * Telegram Message DTO
 * 
 * 表示一条消息
 * 
 * @see https://core.telegram.org/bots/api#message
 */
class Message extends BaseDTO
{
    /**
     * 消息的唯一标识符
     */
    public int $messageId;

    /**
     * 消息线程的唯一标识符；仅适用于超级群组中的消息
     */
    public ?int $messageThreadId = null;

    /**
     * 消息发送者；对于通过频道发送的消息为空
     */
    public ?User $from = null;

    /**
     * 发送消息的机器人的信息
     */
    public ?User $senderChat = null;

    /**
     * 消息发送日期，Unix 时间戳
     */
    public int $date;

    /**
     * 发送消息的聊天
     */
    public Chat $chat;

    /**
     * 转发消息的原始发送者
     */
    public ?User $forwardFrom = null;

    /**
     * 转发消息的原始聊天
     */
    public ?Chat $forwardFromChat = null;

    /**
     * 转发消息的原始消息 ID
     */
    public ?int $forwardFromMessageId = null;

    /**
     * 转发消息的原始消息签名
     */
    public ?string $forwardSignature = null;

    /**
     * 转发消息的原始发送者名称
     */
    public ?string $forwardSenderName = null;

    /**
     * 转发消息的原始发送日期
     */
    public ?int $forwardDate = null;

    /**
     * 如果消息是主题消息的回复，则为 True
     */
    public ?bool $isTopicMessage = null;

    /**
     * 如果消息是自动转发消息，则为 True
     */
    public ?bool $isAutomaticForward = null;

    /**
     * 回复的消息
     */
    public ?Message $replyToMessage = null;

    /**
     * 外部回复信息
     */
    public ?ExternalReplyInfo $externalReply = null;

    /**
     * 引用信息
     */
    public ?TextQuote $quote = null;

    /**
     * 通过机器人发送消息的用户
     */
    public ?User $viaBot = null;

    /**
     * 消息最后编辑日期，Unix 时间戳
     */
    public ?int $editDate = null;

    /**
     * 如果消息具有受保护的内容，则为 True
     */
    public ?bool $hasProtectedContent = null;

    /**
     * 媒体组唯一标识符
     */
    public ?string $mediaGroupId = null;

    /**
     * 消息发送者的签名
     */
    public ?string $authorSignature = null;

    /**
     * 消息文本，对于文本消息，0-4096 个字符
     */
    public ?string $text = null;

    /**
     * 消息中的特殊实体
     */
    public ?array $entities = null;

    /**
     * 链接预览生成选项
     */
    public ?LinkPreviewOptions $linkPreviewOptions = null;

    /**
     * 音频文件信息
     */
    public ?Audio $audio = null;

    /**
     * 通用文件信息
     */
    public ?Document $document = null;

    /**
     * 动画信息
     */
    public ?Animation $animation = null;

    /**
     * 游戏信息
     */
    public ?Game $game = null;

    /**
     * 照片信息
     */
    public ?array $photo = null;

    /**
     * 贴纸信息
     */
    public ?Sticker $sticker = null;

    /**
     * 故事信息
     */
    public ?Story $story = null;

    /**
     * 视频信息
     */
    public ?Video $video = null;

    /**
     * 视频笔记信息
     */
    public ?VideoNote $videoNote = null;

    /**
     * 语音消息信息
     */
    public ?Voice $voice = null;

    /**
     * 标题，针对音频、文档、照片、视频或语音
     */
    public ?string $caption = null;

    /**
     * 标题中的特殊实体
     */
    public ?array $captionEntities = null;

    /**
     * 如果消息媒体被破坏，则为 True
     */
    public ?bool $hasMediaSpoiler = null;

    /**
     * 联系人信息
     */
    public ?Contact $contact = null;

    /**
     * 骰子信息
     */
    public ?Dice $dice = null;

    /**
     * 位置信息
     */
    public ?Location $location = null;

    /**
     * 场所信息
     */
    public ?Venue $venue = null;

    /**
     * 投票信息
     */
    public ?Poll $poll = null;

    /**
     * Web 应用数据
     */
    public ?WebAppData $webAppData = null;

    /**
     * 内联键盘
     */
    public ?InlineKeyboardMarkup $replyMarkup = null;

    /**
     * 验证消息数据
     */
    public function validate(): void
    {
        // 验证消息 ID
        if (!isset($this->messageId)) {
            throw ValidationException::required('messageId');
        }

        if (!is_int($this->messageId) || $this->messageId <= 0) {
            throw ValidationException::invalidType('messageId', 'positive integer', $this->messageId);
        }

        // 验证发送日期
        if (!isset($this->date)) {
            throw ValidationException::required('date');
        }

        if (!is_int($this->date) || $this->date <= 0) {
            throw ValidationException::invalidType('date', 'positive integer', $this->date);
        }

        // 验证聊天信息
        if (!isset($this->chat)) {
            throw ValidationException::required('chat');
        }

        if ($this->chat instanceof Chat) {
            $this->chat->validate();
        }

        // 验证发送者信息（如果存在）
        if ($this->from instanceof User) {
            $this->from->validate();
        }

        // 验证文本长度
        if ($this->text !== null) {
            if (!is_string($this->text)) {
                throw ValidationException::invalidType('text', 'string or null', $this->text);
            }

            if (strlen($this->text) > 4096) {
                throw ValidationException::invalidLength('text', 0, 4096, $this->text);
            }
        }

        // 验证标题长度
        if ($this->caption !== null) {
            if (!is_string($this->caption)) {
                throw ValidationException::invalidType('caption', 'string or null', $this->caption);
            }

            if (strlen($this->caption) > 1024) {
                throw ValidationException::invalidLength('caption', 0, 1024, $this->caption);
            }
        }

        // 验证时间戳字段
        $timestampFields = ['forwardDate', 'editDate'];
        foreach ($timestampFields as $field) {
            if ($this->$field !== null && (!is_int($this->$field) || $this->$field <= 0)) {
                throw ValidationException::invalidType($field, 'positive integer or null', $this->$field);
            }
        }

        // 验证布尔字段
        $booleanFields = ['isTopicMessage', 'isAutomaticForward', 'hasProtectedContent', 'hasMediaSpoiler'];
        foreach ($booleanFields as $field) {
            if ($this->$field !== null && !is_bool($this->$field)) {
                throw ValidationException::invalidType($field, 'boolean or null', $this->$field);
            }
        }

        // 验证嵌套对象
        if ($this->replyToMessage instanceof Message) {
            $this->replyToMessage->validate();
        }

        if ($this->viaBot instanceof User) {
            $this->viaBot->validate();
        }

        if ($this->forwardFrom instanceof User) {
            $this->forwardFrom->validate();
        }

        if ($this->forwardFromChat instanceof Chat) {
            $this->forwardFromChat->validate();
        }

        if ($this->location instanceof Location) {
            $this->location->validate();
        }
    }

    /**
     * 检查是否为文本消息
     */
    public function isText(): bool
    {
        return $this->text !== null && $this->text !== '';
    }

    /**
     * 检查是否为照片消息
     */
    public function isPhoto(): bool
    {
        return $this->photo !== null && !empty($this->photo);
    }

    /**
     * 检查是否为视频消息
     */
    public function isVideo(): bool
    {
        return $this->video !== null;
    }

    /**
     * 检查是否为音频消息
     */
    public function isAudio(): bool
    {
        return $this->audio !== null;
    }

    /**
     * 检查是否为语音消息
     */
    public function isVoice(): bool
    {
        return $this->voice !== null;
    }

    /**
     * 检查是否为文档消息
     */
    public function isDocument(): bool
    {
        return $this->document !== null;
    }

    /**
     * 检查是否为贴纸消息
     */
    public function isSticker(): bool
    {
        return $this->sticker !== null;
    }

    /**
     * 检查是否为动画消息
     */
    public function isAnimation(): bool
    {
        return $this->animation !== null;
    }

    /**
     * 检查是否为位置消息
     */
    public function isLocation(): bool
    {
        return $this->location !== null;
    }

    /**
     * 检查是否为联系人消息
     */
    public function isContact(): bool
    {
        return $this->contact !== null;
    }

    /**
     * 检查是否为投票消息
     */
    public function isPoll(): bool
    {
        return $this->poll !== null;
    }

    /**
     * 检查是否为游戏消息
     */
    public function isGame(): bool
    {
        return $this->game !== null;
    }

    /**
     * 检查是否为转发消息
     */
    public function isForwarded(): bool
    {
        return $this->forwardDate !== null;
    }

    /**
     * 检查是否为回复消息
     */
    public function isReply(): bool
    {
        return $this->replyToMessage !== null;
    }

    /**
     * 检查是否为编辑过的消息
     */
    public function isEdited(): bool
    {
        return $this->editDate !== null;
    }

    /**
     * 检查是否来自机器人
     */
    public function isFromBot(): bool
    {
        return $this->from !== null && $this->from->isBot();
    }

    /**
     * 检查是否来自私人聊天
     */
    public function isPrivate(): bool
    {
        return $this->chat->isPrivate();
    }

    /**
     * 检查是否来自群组聊天
     */
    public function isGroupChat(): bool
    {
        return $this->chat->isGroupChat();
    }

    /**
     * 检查是否来自频道
     */
    public function isChannelPost(): bool
    {
        return $this->chat->isChannel();
    }

    /**
     * 检查是否有受保护的内容
     */
    public function hasProtectedContent(): bool
    {
        return $this->hasProtectedContent === true;
    }

    /**
     * 检查是否有媒体破坏者
     */
    public function hasMediaSpoiler(): bool
    {
        return $this->hasMediaSpoiler === true;
    }

    /**
     * 获取消息内容（文本或标题）
     */
    public function getContent(): ?string
    {
        return $this->text ?? $this->caption;
    }

    /**
     * 获取消息实体（文本或标题实体）
     */
    public function getEntities(): ?array
    {
        return $this->entities ?? $this->captionEntities;
    }

    /**
     * 获取发送日期的 DateTime 对象
     */
    public function getDate(): DateTime
    {
        return (new DateTime())->setTimestamp($this->date);
    }

    /**
     * 获取编辑日期的 DateTime 对象
     */
    public function getEditDate(): ?DateTime
    {
        return $this->editDate ? (new DateTime())->setTimestamp($this->editDate) : null;
    }

    /**
     * 获取转发日期的 DateTime 对象
     */
    public function getForwardDate(): ?DateTime
    {
        return $this->forwardDate ? (new DateTime())->setTimestamp($this->forwardDate) : null;
    }

    /**
     * 获取发送者显示名称
     */
    public function getSenderName(): string
    {
        if ($this->from) {
            return $this->from->getDisplayName();
        }

        if ($this->senderChat) {
            return $this->senderChat->getDisplayName();
        }

        return 'Unknown';
    }

    /**
     * 获取消息链接
     */
    public function getMessageUrl(): ?string
    {
        if ($this->chat->username) {
            return "https://t.me/{$this->chat->username}/{$this->messageId}";
        }

        // 对于私人聊天或没有用户名的群组，无法生成公开链接
        return null;
    }

    /**
     * 比较两条消息是否相同
     */
    public function equals(?Message $other): bool
    {
        return $other !== null 
            && $this->messageId === $other->messageId 
            && $this->chat->equals($other->chat);
    }

    /**
     * 获取消息类型
     */
    public function getType(): string
    {
        if ($this->isText()) return 'text';
        if ($this->isPhoto()) return 'photo';
        if ($this->isVideo()) return 'video';
        if ($this->isAudio()) return 'audio';
        if ($this->isVoice()) return 'voice';
        if ($this->isDocument()) return 'document';
        if ($this->isSticker()) return 'sticker';
        if ($this->isAnimation()) return 'animation';
        if ($this->isLocation()) return 'location';
        if ($this->isContact()) return 'contact';
        if ($this->isPoll()) return 'poll';
        if ($this->isGame()) return 'game';

        return 'unknown';
    }

    /**
     * 检查消息是否包含指定文本
     */
    public function contains(string $text, bool $caseSensitive = false): bool
    {
        $content = $this->getContent();
        
        if ($content === null) {
            return false;
        }

        if ($caseSensitive) {
            return str_contains($content, $text);
        }

        return str_contains(strtolower($content), strtolower($text));
    }

    /**
     * 检查消息是否以指定文本开头
     */
    public function startsWith(string $text, bool $caseSensitive = false): bool
    {
        $content = $this->getContent();
        
        if ($content === null) {
            return false;
        }

        if ($caseSensitive) {
            return str_starts_with($content, $text);
        }

        return str_starts_with(strtolower($content), strtolower($text));
    }

    /**
     * 检查消息是否以指定文本结尾
     */
    public function endsWith(string $text, bool $caseSensitive = false): bool
    {
        $content = $this->getContent();
        
        if ($content === null) {
            return false;
        }

        if ($caseSensitive) {
            return str_ends_with($content, $text);
        }

        return str_ends_with(strtolower($content), strtolower($text));
    }
}

/**
 * 消息实体 DTO
 */
class MessageEntity extends BaseDTO
{
    public const TYPE_MENTION = 'mention';
    public const TYPE_HASHTAG = 'hashtag';
    public const TYPE_CASHTAG = 'cashtag';
    public const TYPE_BOT_COMMAND = 'bot_command';
    public const TYPE_URL = 'url';
    public const TYPE_EMAIL = 'email';
    public const TYPE_PHONE_NUMBER = 'phone_number';
    public const TYPE_BOLD = 'bold';
    public const TYPE_ITALIC = 'italic';
    public const TYPE_UNDERLINE = 'underline';
    public const TYPE_STRIKETHROUGH = 'strikethrough';
    public const TYPE_SPOILER = 'spoiler';
    public const TYPE_CODE = 'code';
    public const TYPE_PRE = 'pre';
    public const TYPE_TEXT_LINK = 'text_link';
    public const TYPE_TEXT_MENTION = 'text_mention';
    public const TYPE_CUSTOM_EMOJI = 'custom_emoji';

    public string $type;
    public int $offset;
    public int $length;
    public ?string $url = null;
    public ?User $user = null;
    public ?string $language = null;
    public ?string $customEmojiId = null;

    public function validate(): void
    {
        if (!isset($this->type) || !is_string($this->type)) {
            throw ValidationException::required('type');
        }

        if (!isset($this->offset) || !is_int($this->offset) || $this->offset < 0) {
            throw ValidationException::invalidType('offset', 'non-negative integer', $this->offset);
        }

        if (!isset($this->length) || !is_int($this->length) || $this->length <= 0) {
            throw ValidationException::invalidType('length', 'positive integer', $this->length);
        }
    }
}

// 其他相关的 DTO 类定义...
class ExternalReplyInfo extends BaseDTO
{
    public ?MessageOrigin $origin = null;
    public ?Chat $chat = null;
    public ?int $messageId = null;
    public ?LinkPreviewOptions $linkPreviewOptions = null;
    public ?Animation $animation = null;
    public ?Audio $audio = null;
    public ?Document $document = null;
    public ?array $photo = null;
    public ?Sticker $sticker = null;
    public ?Story $story = null;
    public ?Video $video = null;
    public ?VideoNote $videoNote = null;
    public ?Voice $voice = null;
    public ?bool $hasMediaSpoiler = null;
    public ?Contact $contact = null;
    public ?Dice $dice = null;
    public ?Game $game = null;
    public ?Giveaway $giveaway = null;
    public ?GiveawayWinners $giveawayWinners = null;
    public ?Invoice $invoice = null;
    public ?Location $location = null;
    public ?Poll $poll = null;
    public ?Venue $venue = null;
}

class TextQuote extends BaseDTO
{
    public string $text;
    public ?array $entities = null;
    public int $position;
    public ?bool $isManual = null;

    public function validate(): void
    {
        if (!isset($this->text) || !is_string($this->text)) {
            throw ValidationException::required('text');
        }

        if (!isset($this->position) || !is_int($this->position) || $this->position < 0) {
            throw ValidationException::invalidType('position', 'non-negative integer', $this->position);
        }
    }
}

class LinkPreviewOptions extends BaseDTO
{
    public ?bool $isDisabled = null;
    public ?string $url = null;
    public ?bool $preferSmallMedia = null;
    public ?bool $preferLargeMedia = null;
    public ?bool $showAboveText = null;
}

// 由于篇幅限制，这里省略其他媒体相关的 DTO 类定义
// 实际实现中需要包含 Audio、Document、Animation、Video 等完整的媒体 DTO 类