<?php

declare(strict_types=1);

namespace XBot\Telegram\Models\DTO;

use XBot\Telegram\Contracts\DTOInterface;

/**
 * 内联查询对象
 * 
 * 表示用户发送的内联查询
 */
class InlineQuery extends BaseDTO implements DTOInterface
{
    /**
     * 内联查询的唯一标识符
     */
    public readonly string $id;

    /**
     * 发送查询的用户
     */
    public readonly User $from;

    /**
     * 查询字符串（最多256个字符）
     */
    public readonly string $query;

    /**
     * 查询结果的偏移量，可以由机器人控制
     */
    public readonly string $offset;

    /**
     * 查询的聊天类型（可选）
     * 只有在请求中提供了 chat_type 时才会包含
     */
    public readonly ?string $chatType;

    /**
     * 发送查询的用户位置（可选）
     * 只有在用户同意分享位置时才会包含
     */
    public readonly ?Location $location;

    public function __construct(
        string $id,
        User $from,
        string $query,
        string $offset,
        ?string $chatType = null,
        ?Location $location = null
    ) {
        $this->id = $id;
        $this->from = $from;
        $this->query = $query;
        $this->offset = $offset;
        $this->chatType = $chatType;
        $this->location = $location;

        parent::__construct();
    }

    /**
     * 从数组创建 InlineQuery 实例
     */
    public static function fromArray(array $data): static
    {
        return new static(
            id: $data['id'] ?? '',
            from: isset($data['from']) && is_array($data['from']) 
                ? User::fromArray($data['from']) 
                : new User(0, false, ''),
            query: $data['query'] ?? '',
            offset: $data['offset'] ?? '',
            chatType: $data['chat_type'] ?? null,
            location: isset($data['location']) && is_array($data['location']) 
                ? Location::fromArray($data['location']) 
                : null
        );
    }

    /**
     * 验证内联查询数据
     */
    public function validate(): void
    {
        if (empty($this->id)) {
            throw new \InvalidArgumentException('Inline query ID is required');
        }

        if (strlen($this->query) > 256) {
            throw new \InvalidArgumentException('Query string cannot exceed 256 characters');
        }

        $this->from->validate();

        if ($this->location) {
            $this->location->validate();
        }

        if ($this->chatType && !in_array($this->chatType, ['sender', 'private', 'group', 'supergroup', 'channel'])) {
            throw new \InvalidArgumentException('Invalid chat type');
        }
    }

    /**
     * 检查查询是否为空
     */
    public function isEmpty(): bool
    {
        return trim($this->query) === '';
    }

    /**
     * 检查是否有位置信息
     */
    public function hasLocation(): bool
    {
        return $this->location !== null;
    }

    /**
     * 检查是否指定了聊天类型
     */
    public function hasChatType(): bool
    {
        return $this->chatType !== null;
    }

    /**
     * 获取查询字符串的长度
     */
    public function getQueryLength(): int
    {
        return strlen($this->query);
    }

    /**
     * 获取清理后的查询字符串（去除前后空格）
     */
    public function getCleanQuery(): string
    {
        return trim($this->query);
    }

    /**
     * 将查询字符串分割为单词
     */
    public function getQueryWords(): array
    {
        $cleanQuery = $this->getCleanQuery();
        if ($cleanQuery === '') {
            return [];
        }

        return array_filter(explode(' ', $cleanQuery), fn($word) => trim($word) !== '');
    }

    /**
     * 获取查询的第一个单词
     */
    public function getFirstWord(): ?string
    {
        $words = $this->getQueryWords();
        return !empty($words) ? $words[0] : null;
    }

    /**
     * 检查查询是否以指定字符串开头
     */
    public function startsWith(string $prefix): bool
    {
        return str_starts_with(strtolower($this->getCleanQuery()), strtolower($prefix));
    }

    /**
     * 检查查询是否包含指定字符串
     */
    public function contains(string $needle): bool
    {
        return str_contains(strtolower($this->getCleanQuery()), strtolower($needle));
    }

    /**
     * 检查查询是否匹配指定模式
     */
    public function matches(string $pattern): bool
    {
        return preg_match($pattern, $this->query) === 1;
    }

    /**
     * 检查查询是否为命令格式（以@开头）
     */
    public function isCommand(): bool
    {
        return str_starts_with($this->getCleanQuery(), '@');
    }

    /**
     * 提取命令名（如果是命令格式）
     */
    public function getCommand(): ?string
    {
        if (!$this->isCommand()) {
            return null;
        }

        $command = $this->getFirstWord();
        return $command ? substr($command, 1) : null; // 移除 @ 符号
    }

    /**
     * 获取命令参数（如果是命令格式）
     */
    public function getCommandArgs(): array
    {
        if (!$this->isCommand()) {
            return [];
        }

        $words = $this->getQueryWords();
        return array_slice($words, 1); // 移除第一个单词（命令）
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
     * 检查是否来自机器人
     */
    public function isFromBot(): bool
    {
        return $this->from->isBot;
    }

    /**
     * 获取建议的缓存时间（基于查询类型）
     */
    public function getSuggestedCacheTime(): int
    {
        // 空查询或很短的查询：短缓存
        if ($this->isEmpty() || $this->getQueryLength() < 3) {
            return 60; // 1分钟
        }

        // 命令查询：中等缓存
        if ($this->isCommand()) {
            return 300; // 5分钟
        }

        // 位置相关查询：短缓存
        if ($this->hasLocation()) {
            return 120; // 2分钟
        }

        // 一般文本查询：长缓存
        return 600; // 10分钟
    }

    /**
     * 获取建议的结果数量限制
     */
    public function getSuggestedLimit(): int
    {
        // 空查询：返回较少结果
        if ($this->isEmpty()) {
            return 10;
        }

        // 短查询：返回更多结果
        if ($this->getQueryLength() < 5) {
            return 25;
        }

        // 长查询：返回最多结果
        return 50;
    }

    /**
     * 创建回应此内联查询的基础参数
     */
    public function createAnswerParameters(
        array $results,
        ?int $cacheTime = null,
        ?bool $isPersonal = null,
        ?string $nextOffset = null,
        ?string $switchPmText = null,
        ?string $switchPmParameter = null
    ): array {
        $params = [
            'inline_query_id' => $this->id,
            'results' => json_encode($results),
        ];

        if ($cacheTime !== null) {
            $params['cache_time'] = $cacheTime;
        } else {
            $params['cache_time'] = $this->getSuggestedCacheTime();
        }

        if ($isPersonal !== null) {
            $params['is_personal'] = $isPersonal;
        }

        if ($nextOffset !== null) {
            $params['next_offset'] = $nextOffset;
        }

        if ($switchPmText !== null) {
            $params['switch_pm_text'] = $switchPmText;
        }

        if ($switchPmParameter !== null) {
            $params['switch_pm_parameter'] = $switchPmParameter;
        }

        return $params;
    }

    /**
     * 获取查询分析信息
     */
    public function getQueryAnalysis(): array
    {
        return [
            'length' => $this->getQueryLength(),
            'is_empty' => $this->isEmpty(),
            'is_command' => $this->isCommand(),
            'word_count' => count($this->getQueryWords()),
            'first_word' => $this->getFirstWord(),
            'command' => $this->getCommand(),
            'command_args' => $this->getCommandArgs(),
            'suggested_cache_time' => $this->getSuggestedCacheTime(),
            'suggested_limit' => $this->getSuggestedLimit(),
        ];
    }

    /**
     * 获取完整的内联查询信息
     */
    public function getInlineQueryInfo(): array
    {
        return [
            'id' => $this->id,
            'query' => $this->query,
            'clean_query' => $this->getCleanQuery(),
            'offset' => $this->offset,
            'chat_type' => $this->chatType,
            'user' => $this->from->toArray(),
            'location' => $this->location?->toArray(),
            'has_location' => $this->hasLocation(),
            'has_chat_type' => $this->hasChatType(),
            'analysis' => $this->getQueryAnalysis(),
        ];
    }

    /**
     * 字符串表示
     */
    public function __toString(): string
    {
        $info = [
            "InlineQuery #{$this->id}",
            "from {$this->getUserDisplayName()}",
        ];

        if (!$this->isEmpty()) {
            $displayQuery = strlen($this->query) > 30 
                ? substr($this->query, 0, 30) . '...' 
                : $this->query;
            $info[] = "query: \"{$displayQuery}\"";
        } else {
            $info[] = "empty query";
        }

        if ($this->hasLocation()) {
            $info[] = "with location";
        }

        if ($this->hasChatType()) {
            $info[] = "chat_type: {$this->chatType}";
        }

        return implode(' - ', $info);
    }
}