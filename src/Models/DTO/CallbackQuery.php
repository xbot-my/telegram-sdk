<?php

declare(strict_types=1);

namespace XBot\Telegram\Models\DTO;

use XBot\Telegram\Contracts\DTOInterface;

/**
 * 回调查询对象
 * 
 * 表示内联键盘按钮点击产生的回调查询
 */
class CallbackQuery extends BaseDTO implements DTOInterface
{
    /**
     * 回调查询的唯一标识符
     */
    public readonly string $id;

    /**
     * 发送回调查询的用户
     */
    public readonly User $from;

    /**
     * 包含按钮的消息（可选）
     * 如果按钮附加到通过内联模式发送的消息，则可能不存在
     */
    public readonly ?Message $message;

    /**
     * 内联消息的标识符（可选）
     * 如果按钮附加到通过内联模式发送的消息
     */
    public readonly ?string $inlineMessageId;

    /**
     * 聊天实例的全局标识符
     * 用于区分来自同一用户的不同聊天的查询
     */
    public readonly string $chatInstance;

    /**
     * 与按钮关联的数据（可选）
     */
    public readonly ?string $data;

    /**
     * 与按钮关联的游戏短名称（可选）
     */
    public readonly ?string $gameShortName;

    public function __construct(
        string $id,
        User $from,
        string $chatInstance,
        ?Message $message = null,
        ?string $inlineMessageId = null,
        ?string $data = null,
        ?string $gameShortName = null
    ) {
        $this->id = $id;
        $this->from = $from;
        $this->chatInstance = $chatInstance;
        $this->message = $message;
        $this->inlineMessageId = $inlineMessageId;
        $this->data = $data;
        $this->gameShortName = $gameShortName;

        parent::__construct();
    }

    /**
     * 从数组创建 CallbackQuery 实例
     */
    public static function fromArray(array $data): static
    {
        return new static(
            id: $data['id'] ?? '',
            from: isset($data['from']) && is_array($data['from']) 
                ? User::fromArray($data['from']) 
                : new User(0, false, ''),
            chatInstance: $data['chat_instance'] ?? '',
            message: isset($data['message']) && is_array($data['message']) 
                ? Message::fromArray($data['message']) 
                : null,
            inlineMessageId: $data['inline_message_id'] ?? null,
            data: $data['data'] ?? null,
            gameShortName: $data['game_short_name'] ?? null
        );
    }

    /**
     * 验证回调查询数据
     */
    public function validate(): void
    {
        if (empty($this->id)) {
            throw new \InvalidArgumentException('Callback query ID is required');
        }

        if (empty($this->chatInstance)) {
            throw new \InvalidArgumentException('Chat instance is required');
        }

        $this->from->validate();

        if ($this->message) {
            $this->message->validate();
        }
    }

    /**
     * 检查是否来自内联消息
     */
    public function isFromInlineMessage(): bool
    {
        return $this->inlineMessageId !== null;
    }

    /**
     * 检查是否来自聊天消息
     */
    public function isFromChatMessage(): bool
    {
        return $this->message !== null;
    }

    /**
     * 检查是否有关联数据
     */
    public function hasData(): bool
    {
        return $this->data !== null;
    }

    /**
     * 检查是否为游戏回调
     */
    public function isGameCallback(): bool
    {
        return $this->gameShortName !== null;
    }

    /**
     * 获取聊天 ID（如果来自聊天消息）
     */
    public function getChatId(): ?int
    {
        return $this->message?->chat?->id;
    }

    /**
     * 获取消息 ID（如果来自聊天消息）
     */
    public function getMessageId(): ?int
    {
        return $this->message?->messageId;
    }

    /**
     * 解析数据为数组（假设数据是 JSON 格式）
     */
    public function getDataAsArray(): ?array
    {
        if ($this->data === null) {
            return null;
        }

        $decoded = json_decode($this->data, true);
        return is_array($decoded) ? $decoded : null;
    }

    /**
     * 解析数据为键值对（假设数据格式为 key=value&key2=value2）
     */
    public function getDataAsKeyValue(): ?array
    {
        if ($this->data === null) {
            return null;
        }

        $result = [];
        parse_str($this->data, $result);
        
        return !empty($result) ? $result : null;
    }

    /**
     * 检查数据是否匹配指定的前缀
     */
    public function dataStartsWith(string $prefix): bool
    {
        return $this->data !== null && str_starts_with($this->data, $prefix);
    }

    /**
     * 检查数据是否包含指定的值
     */
    public function dataContains(string $value): bool
    {
        return $this->data !== null && str_contains($this->data, $value);
    }

    /**
     * 从数据中提取指定的参数（适用于 key=value 格式）
     */
    public function getDataParameter(string $key, ?string $default = null): ?string
    {
        $params = $this->getDataAsKeyValue();
        return $params[$key] ?? $default;
    }

    /**
     * 获取回调查询的类型描述
     */
    public function getType(): string
    {
        return match (true) {
            $this->isGameCallback() => 'game',
            $this->isFromInlineMessage() => 'inline',
            $this->isFromChatMessage() => 'chat',
            default => 'unknown'
        };
    }

    /**
     * 获取用户显示名称
     */
    public function getUserDisplayName(): string
    {
        return $this->from->getDisplayName();
    }

    /**
     * 检查是否来自特定用户
     */
    public function isFromUser(int $userId): bool
    {
        return $this->from->id === $userId;
    }

    /**
     * 检查是否来自管理员（如果有消息上下文）
     */
    public function isFromAdmin(): bool
    {
        if (!$this->message || !$this->message->chat) {
            return false;
        }

        // 这里需要额外的 API 调用来检查用户是否为管理员
        // 在实际使用中，可能需要传入 Bot 实例或管理员列表
        return false;
    }

    /**
     * 获取回调查询的上下文信息
     */
    public function getContext(): array
    {
        $context = [
            'type' => $this->getType(),
            'user_id' => $this->from->id,
            'user_name' => $this->getUserDisplayName(),
            'chat_instance' => $this->chatInstance,
            'has_data' => $this->hasData(),
            'data_length' => $this->data ? strlen($this->data) : 0,
        ];

        if ($this->isFromChatMessage()) {
            $context['chat_id'] = $this->getChatId();
            $context['message_id'] = $this->getMessageId();
            $context['chat_type'] = $this->message->chat->type ?? null;
        }

        if ($this->isFromInlineMessage()) {
            $context['inline_message_id'] = $this->inlineMessageId;
        }

        if ($this->isGameCallback()) {
            $context['game_short_name'] = $this->gameShortName;
        }

        return $context;
    }

    /**
     * 创建回应此回调查询的参数
     */
    public function createAnswerParameters(
        ?string $text = null,
        bool $showAlert = false,
        ?string $url = null,
        ?int $cacheTime = null
    ): array {
        $params = ['callback_query_id' => $this->id];

        if ($text !== null) {
            $params['text'] = $text;
        }

        if ($showAlert) {
            $params['show_alert'] = true;
        }

        if ($url !== null) {
            $params['url'] = $url;
        }

        if ($cacheTime !== null) {
            $params['cache_time'] = $cacheTime;
        }

        return $params;
    }

    /**
     * 获取完整的回调查询信息
     */
    public function getCallbackInfo(): array
    {
        return [
            'id' => $this->id,
            'type' => $this->getType(),
            'user' => $this->from->toArray(),
            'chat_instance' => $this->chatInstance,
            'data' => $this->data,
            'data_as_array' => $this->getDataAsArray(),
            'data_as_key_value' => $this->getDataAsKeyValue(),
            'game_short_name' => $this->gameShortName,
            'inline_message_id' => $this->inlineMessageId,
            'message_id' => $this->getMessageId(),
            'chat_id' => $this->getChatId(),
            'context' => $this->getContext(),
        ];
    }

    /**
     * 字符串表示
     */
    public function __toString(): string
    {
        $info = [
            "CallbackQuery #{$this->id}",
            "from {$this->getUserDisplayName()}",
        ];

        if ($this->hasData()) {
            $info[] = "data: " . substr($this->data, 0, 20) . (strlen($this->data) > 20 ? '...' : '');
        }

        if ($this->isGameCallback()) {
            $info[] = "game: {$this->gameShortName}";
        }

        $info[] = "type: {$this->getType()}";

        return implode(' - ', $info);
    }
}