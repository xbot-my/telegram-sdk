<?php

declare(strict_types=1);

namespace XBot\Telegram\Models\DTO;

use XBot\Telegram\Exceptions\ValidationException;

/**
 * Telegram Update DTO
 * 
 * 表示 Telegram 的更新信息
 * 
 * @see https://core.telegram.org/bots/api#update
 */
class Update extends BaseDTO
{
    /**
     * 更新的唯一标识符
     */
    public int $updateId;

    /**
     * 新的传入消息
     */
    public ?Message $message = null;

    /**
     * 新版本的已知消息
     */
    public ?Message $editedMessage = null;

    /**
     * 新的传入频道帖子
     */
    public ?Message $channelPost = null;

    /**
     * 新版本的已知频道帖子
     */
    public ?Message $editedChannelPost = null;

    /**
     * 商业连接的新传入消息
     */
    public ?BusinessConnection $businessConnection = null;

    /**
     * 来自连接的商业账户的新消息
     */
    public ?Message $businessMessage = null;

    /**
     * 来自连接的商业账户的编辑消息
     */
    public ?Message $editedBusinessMessage = null;

    /**
     * 从商业账户中删除的消息
     */
    public ?BusinessMessagesDeleted $deletedBusinessMessages = null;

    /**
     * 消息反应的新传入信息
     */
    public ?MessageReactionUpdated $messageReaction = null;

    /**
     * 消息反应计数的新传入信息
     */
    public ?MessageReactionCountUpdated $messageReactionCount = null;

    /**
     * 新的传入内联查询
     */
    public ?InlineQuery $inlineQuery = null;

    /**
     * 新的传入内联查询选择结果
     */
    public ?ChosenInlineResult $chosenInlineResult = null;

    /**
     * 新的传入回调查询
     */
    public ?CallbackQuery $callbackQuery = null;

    /**
     * 新的传入运输查询
     */
    public ?ShippingQuery $shippingQuery = null;

    /**
     * 新的传入预结账查询
     */
    public ?PreCheckoutQuery $preCheckoutQuery = null;

    /**
     * 新的投票状态
     */
    public ?Poll $poll = null;

    /**
     * 用户在非匿名投票中改变了答案
     */
    public ?PollAnswer $pollAnswer = null;

    /**
     * 机器人的聊天成员状态更新
     */
    public ?ChatMemberUpdated $myChatMember = null;

    /**
     * 聊天成员状态更新
     */
    public ?ChatMemberUpdated $chatMember = null;

    /**
     * 聊天加入请求
     */
    public ?ChatJoinRequest $chatJoinRequest = null;

    /**
     * 聊天提升状态更新
     */
    public ?ChatBoostUpdated $chatBoost = null;

    /**
     * 聊天提升被移除
     */
    public ?ChatBoostRemoved $removedChatBoost = null;

    /**
     * 验证更新数据
     */
    public function validate(): void
    {
        // 验证更新 ID
        if (!isset($this->updateId)) {
            throw ValidationException::required('updateId');
        }

        if (!is_int($this->updateId) || $this->updateId < 0) {
            throw ValidationException::invalidType('updateId', 'non-negative integer', $this->updateId);
        }

        // 验证至少有一个更新类型
        $updateTypes = [
            'message', 'editedMessage', 'channelPost', 'editedChannelPost',
            'businessConnection', 'businessMessage', 'editedBusinessMessage', 'deletedBusinessMessages',
            'messageReaction', 'messageReactionCount', 'inlineQuery', 'chosenInlineResult',
            'callbackQuery', 'shippingQuery', 'preCheckoutQuery', 'poll', 'pollAnswer',
            'myChatMember', 'chatMember', 'chatJoinRequest', 'chatBoost', 'removedChatBoost'
        ];

        $hasUpdate = false;
        foreach ($updateTypes as $type) {
            if ($this->$type !== null) {
                $hasUpdate = true;
                
                // 验证嵌套对象
                if (method_exists($this->$type, 'validate')) {
                    $this->$type->validate();
                }
            }
        }

        if (!$hasUpdate) {
            throw ValidationException::invalidType('update', 'at least one update type', null);
        }
    }

    /**
     * 获取更新类型
     */
    public function getType(): string
    {
        if ($this->message !== null) return 'message';
        if ($this->editedMessage !== null) return 'edited_message';
        if ($this->channelPost !== null) return 'channel_post';
        if ($this->editedChannelPost !== null) return 'edited_channel_post';
        if ($this->businessConnection !== null) return 'business_connection';
        if ($this->businessMessage !== null) return 'business_message';
        if ($this->editedBusinessMessage !== null) return 'edited_business_message';
        if ($this->deletedBusinessMessages !== null) return 'deleted_business_messages';
        if ($this->messageReaction !== null) return 'message_reaction';
        if ($this->messageReactionCount !== null) return 'message_reaction_count';
        if ($this->inlineQuery !== null) return 'inline_query';
        if ($this->chosenInlineResult !== null) return 'chosen_inline_result';
        if ($this->callbackQuery !== null) return 'callback_query';
        if ($this->shippingQuery !== null) return 'shipping_query';
        if ($this->preCheckoutQuery !== null) return 'pre_checkout_query';
        if ($this->poll !== null) return 'poll';
        if ($this->pollAnswer !== null) return 'poll_answer';
        if ($this->myChatMember !== null) return 'my_chat_member';
        if ($this->chatMember !== null) return 'chat_member';
        if ($this->chatJoinRequest !== null) return 'chat_join_request';
        if ($this->chatBoost !== null) return 'chat_boost';
        if ($this->removedChatBoost !== null) return 'removed_chat_boost';

        return 'unknown';
    }

    /**
     * 获取更新的消息（如果存在）
     */
    public function getMessage(): ?Message
    {
        return $this->message 
            ?? $this->editedMessage 
            ?? $this->channelPost 
            ?? $this->editedChannelPost
            ?? $this->businessMessage
            ?? $this->editedBusinessMessage;
    }

    /**
     * 获取更新的聊天（如果存在）
     */
    public function getChat(): ?Chat
    {
        $message = $this->getMessage();
        if ($message) {
            return $message->chat;
        }

        if ($this->callbackQuery && $this->callbackQuery->message) {
            return $this->callbackQuery->message->chat;
        }

        if ($this->myChatMember) {
            return $this->myChatMember->chat;
        }

        if ($this->chatMember) {
            return $this->chatMember->chat;
        }

        if ($this->chatJoinRequest) {
            return $this->chatJoinRequest->chat;
        }

        return null;
    }

    /**
     * 获取更新的用户（如果存在）
     */
    public function getUser(): ?User
    {
        $message = $this->getMessage();
        if ($message && $message->from) {
            return $message->from;
        }

        if ($this->inlineQuery) {
            return $this->inlineQuery->from;
        }

        if ($this->chosenInlineResult) {
            return $this->chosenInlineResult->from;
        }

        if ($this->callbackQuery) {
            return $this->callbackQuery->from;
        }

        if ($this->shippingQuery) {
            return $this->shippingQuery->from;
        }

        if ($this->preCheckoutQuery) {
            return $this->preCheckoutQuery->from;
        }

        if ($this->pollAnswer) {
            return $this->pollAnswer->user;
        }

        if ($this->myChatMember) {
            return $this->myChatMember->from;
        }

        if ($this->chatMember) {
            return $this->chatMember->from;
        }

        if ($this->chatJoinRequest) {
            return $this->chatJoinRequest->from;
        }

        return null;
    }

    /**
     * 检查是否为消息更新
     */
    public function isMessage(): bool
    {
        return $this->message !== null;
    }

    /**
     * 检查是否为编辑消息更新
     */
    public function isEditedMessage(): bool
    {
        return $this->editedMessage !== null;
    }

    /**
     * 检查是否为频道帖子更新
     */
    public function isChannelPost(): bool
    {
        return $this->channelPost !== null;
    }

    /**
     * 检查是否为编辑频道帖子更新
     */
    public function isEditedChannelPost(): bool
    {
        return $this->editedChannelPost !== null;
    }

    /**
     * 检查是否为内联查询更新
     */
    public function isInlineQuery(): bool
    {
        return $this->inlineQuery !== null;
    }

    /**
     * 检查是否为回调查询更新
     */
    public function isCallbackQuery(): bool
    {
        return $this->callbackQuery !== null;
    }

    /**
     * 检查是否为投票更新
     */
    public function isPoll(): bool
    {
        return $this->poll !== null;
    }

    /**
     * 检查是否为投票答案更新
     */
    public function isPollAnswer(): bool
    {
        return $this->pollAnswer !== null;
    }

    /**
     * 检查是否为聊天成员更新
     */
    public function isChatMember(): bool
    {
        return $this->myChatMember !== null || $this->chatMember !== null;
    }

    /**
     * 检查是否为聊天加入请求
     */
    public function isChatJoinRequest(): bool
    {
        return $this->chatJoinRequest !== null;
    }

    /**
     * 检查是否为商业相关更新
     */
    public function isBusinessUpdate(): bool
    {
        return $this->businessConnection !== null 
            || $this->businessMessage !== null 
            || $this->editedBusinessMessage !== null 
            || $this->deletedBusinessMessages !== null;
    }

    /**
     * 检查是否为支付相关更新
     */
    public function isPaymentUpdate(): bool
    {
        return $this->shippingQuery !== null || $this->preCheckoutQuery !== null;
    }
}

/**
 * Callback Query DTO
 */
class CallbackQuery extends BaseDTO
{
    public string $id;
    public User $from;
    public ?Message $message = null;
    public ?string $inlineMessageId = null;
    public string $chatInstance;
    public ?string $data = null;
    public ?string $gameShortName = null;

    public function validate(): void
    {
        if (!isset($this->id) || !is_string($this->id)) {
            throw ValidationException::required('id');
        }

        if (!isset($this->from)) {
            throw ValidationException::required('from');
        }

        if ($this->from instanceof User) {
            $this->from->validate();
        }

        if (!isset($this->chatInstance) || !is_string($this->chatInstance)) {
            throw ValidationException::required('chatInstance');
        }
    }

    public function isFromInlineMessage(): bool
    {
        return $this->inlineMessageId !== null;
    }

    public function hasData(): bool
    {
        return $this->data !== null && $this->data !== '';
    }

    public function isGameCallback(): bool
    {
        return $this->gameShortName !== null;
    }
}

/**
 * Inline Query DTO
 */
class InlineQuery extends BaseDTO
{
    public string $id;
    public User $from;
    public string $query;
    public string $offset;
    public ?string $chatType = null;
    public ?Location $location = null;

    public function validate(): void
    {
        $requiredFields = ['id', 'from', 'query', 'offset'];
        
        foreach ($requiredFields as $field) {
            if (!isset($this->$field)) {
                throw ValidationException::required($field);
            }
        }

        if ($this->from instanceof User) {
            $this->from->validate();
        }

        if ($this->location instanceof Location) {
            $this->location->validate();
        }
    }

    public function hasQuery(): bool
    {
        return $this->query !== '';
    }

    public function hasLocation(): bool
    {
        return $this->location !== null;
    }
}

/**
 * Chosen Inline Result DTO
 */
class ChosenInlineResult extends BaseDTO
{
    public string $resultId;
    public User $from;
    public ?Location $location = null;
    public ?string $inlineMessageId = null;
    public string $query;

    public function validate(): void
    {
        $requiredFields = ['resultId', 'from', 'query'];
        
        foreach ($requiredFields as $field) {
            if (!isset($this->$field) || (is_string($this->$field) && $this->$field === '')) {
                throw ValidationException::required($field);
            }
        }

        if ($this->from instanceof User) {
            $this->from->validate();
        }

        if ($this->location instanceof Location) {
            $this->location->validate();
        }
    }
}

// 其他相关的 DTO 类的简化定义
class BusinessConnection extends BaseDTO
{
    public string $id;
    public User $user;
    public int $userChatId;
    public int $date;
    public bool $canReply;
    public bool $isEnabled;
}

class BusinessMessagesDeleted extends BaseDTO
{
    public string $businessConnectionId;
    public Chat $chat;
    public array $messageIds;
}

class MessageReactionUpdated extends BaseDTO
{
    public Chat $chat;
    public int $messageId;
    public ?User $user = null;
    public ?Chat $actorChat = null;
    public int $date;
    public array $oldReaction;
    public array $newReaction;
}

class MessageReactionCountUpdated extends BaseDTO
{
    public Chat $chat;
    public int $messageId;
    public int $date;
    public array $reactions;
}

class ShippingQuery extends BaseDTO
{
    public string $id;
    public User $from;
    public string $invoicePayload;
    public ShippingAddress $shippingAddress;
}

class PreCheckoutQuery extends BaseDTO
{
    public string $id;
    public User $from;
    public string $currency;
    public int $totalAmount;
    public string $invoicePayload;
    public ?string $shippingOptionId = null;
    public ?OrderInfo $orderInfo = null;
}

class Poll extends BaseDTO
{
    public string $id;
    public string $question;
    public array $options;
    public int $totalVoterCount;
    public bool $isClosed;
    public bool $isAnonymous;
    public string $type;
    public bool $allowsMultipleAnswers;
    public ?int $correctOptionId = null;
    public ?string $explanation = null;
    public ?array $explanationEntities = null;
    public ?int $openPeriod = null;
    public ?int $closeDate = null;
}

class PollAnswer extends BaseDTO
{
    public string $pollId;
    public ?Chat $voterChat = null;
    public ?User $user = null;
    public array $optionIds;
}

class ChatMemberUpdated extends BaseDTO
{
    public Chat $chat;
    public User $from;
    public int $date;
    public ChatMember $oldChatMember;
    public ChatMember $newChatMember;
    public ?ChatInviteLink $inviteLink = null;
    public ?bool $viaJoinRequest = null;
    public ?bool $viaChatFolderInviteLink = null;
}

class ChatJoinRequest extends BaseDTO
{
    public Chat $chat;
    public User $from;
    public int $userChatId;
    public int $date;
    public ?string $bio = null;
    public ?ChatInviteLink $inviteLink = null;
}

class ChatBoostUpdated extends BaseDTO
{
    public Chat $chat;
    public ChatBoost $boost;
}

class ChatBoostRemoved extends BaseDTO
{
    public Chat $chat;
    public string $boostId;
    public int $removeDate;
    public ChatBoostSource $source;
}

// 简化的其他支持类
class ChatMember extends BaseDTO
{
    public string $status;
    public User $user;
}

class ChatInviteLink extends BaseDTO
{
    public string $inviteLink;
    public User $creator;
    public bool $createsJoinRequest;
    public bool $isPrimary;
    public bool $isRevoked;
}

class ChatBoost extends BaseDTO
{
    public string $boostId;
    public int $addDate;
    public int $expirationDate;
    public ChatBoostSource $source;
}

class ChatBoostSource extends BaseDTO
{
    public string $source;
    public ?User $user = null;
}

class ShippingAddress extends BaseDTO
{
    public string $countryCode;
    public string $state;
    public string $city;
    public string $streetLine1;
    public string $streetLine2;
    public string $postCode;
}

class OrderInfo extends BaseDTO
{
    public ?string $name = null;
    public ?string $phoneNumber = null;
    public ?string $email = null;
    public ?ShippingAddress $shippingAddress = null;
}