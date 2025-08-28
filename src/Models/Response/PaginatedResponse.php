<?php

declare(strict_types=1);

namespace XBot\Telegram\Models\Response;

/**
 * 分页响应类
 * 
 * 用于处理需要分页的 API 响应结果
 */
class PaginatedResponse
{
    /**
     * 当前页的数据项
     */
    private array $items;

    /**
     * 当前偏移量
     */
    private int $offset;

    /**
     * 每页大小限制
     */
    private int $limit;

    /**
     * 是否还有更多数据
     */
    private bool $hasMore;

    /**
     * 总数量（可选）
     */
    private ?int $totalCount;

    /**
     * 下一页的偏移量（可选）
     */
    private ?int $nextOffset;

    /**
     * 上一页的偏移量（可选）
     */
    private ?int $previousOffset;

    /**
     * 当前页码（从1开始）
     */
    private ?int $currentPage;

    /**
     * 总页数（可选）
     */
    private ?int $totalPages;

    public function __construct(
        array $items,
        int $offset = 0,
        int $limit = 50,
        bool $hasMore = false,
        ?int $totalCount = null,
        ?int $nextOffset = null,
        ?int $previousOffset = null
    ) {
        $this->items = $items;
        $this->offset = max(0, $offset);
        $this->limit = max(1, $limit);
        $this->hasMore = $hasMore;
        $this->totalCount = $totalCount;
        $this->nextOffset = $nextOffset;
        $this->previousOffset = $previousOffset;
        
        // 计算页码信息
        $this->currentPage = intdiv($this->offset, $this->limit) + 1;
        
        if ($this->totalCount !== null) {
            $this->totalPages = max(1, (int) ceil($this->totalCount / $this->limit));
        } else {
            $this->totalPages = null;
        }
    }

    /**
     * 从 API 响应创建分页响应
     */
    public static function fromApiResponse(
        array $data,
        int $offset = 0,
        int $limit = 50,
        ?string $itemsKey = null
    ): static {
        // 如果指定了 itemsKey，使用该键的值作为 items
        if ($itemsKey && isset($data[$itemsKey])) {
            $items = is_array($data[$itemsKey]) ? $data[$itemsKey] : [];
        } else {
            // 否则使用整个 data 数组作为 items
            $items = is_array($data) ? $data : [];
        }

        $totalCount = isset($data['total_count']) ? (int) $data['total_count'] : null;
        $hasMore = count($items) >= $limit;

        // 如果有总数且当前偏移量 + 当前项数 >= 总数，则没有更多数据
        if ($totalCount !== null && ($offset + count($items)) >= $totalCount) {
            $hasMore = false;
        }

        $nextOffset = $hasMore ? $offset + count($items) : null;
        $previousOffset = $offset > 0 ? max(0, $offset - $limit) : null;

        return new static(
            items: $items,
            offset: $offset,
            limit: $limit,
            hasMore: $hasMore,
            totalCount: $totalCount,
            nextOffset: $nextOffset,
            previousOffset: $previousOffset
        );
    }

    /**
     * 获取当前页的数据项
     */
    public function getItems(): array
    {
        return $this->items;
    }

    /**
     * 获取数据项数量
     */
    public function getItemsCount(): int
    {
        return count($this->items);
    }

    /**
     * 获取当前偏移量
     */
    public function getOffset(): int
    {
        return $this->offset;
    }

    /**
     * 获取每页大小限制
     */
    public function getLimit(): int
    {
        return $this->limit;
    }

    /**
     * 是否还有更多数据
     */
    public function hasMore(): bool
    {
        return $this->hasMore;
    }

    /**
     * 获取总数量
     */
    public function getTotalCount(): ?int
    {
        return $this->totalCount;
    }

    /**
     * 获取下一页的偏移量
     */
    public function getNextOffset(): ?int
    {
        return $this->nextOffset;
    }

    /**
     * 获取上一页的偏移量
     */
    public function getPreviousOffset(): ?int
    {
        return $this->previousOffset;
    }

    /**
     * 获取当前页码
     */
    public function getCurrentPage(): int
    {
        return $this->currentPage;
    }

    /**
     * 获取总页数
     */
    public function getTotalPages(): ?int
    {
        return $this->totalPages;
    }

    /**
     * 是否有上一页
     */
    public function hasPrevious(): bool
    {
        return $this->offset > 0;
    }

    /**
     * 是否有下一页
     */
    public function hasNext(): bool
    {
        return $this->hasMore;
    }

    /**
     * 是否为第一页
     */
    public function isFirstPage(): bool
    {
        return $this->offset === 0;
    }

    /**
     * 是否为最后一页
     */
    public function isLastPage(): bool
    {
        if ($this->totalCount === null) {
            return !$this->hasMore;
        }

        return $this->currentPage === $this->totalPages;
    }

    /**
     * 是否为空结果
     */
    public function isEmpty(): bool
    {
        return empty($this->items);
    }

    /**
     * 获取分页统计信息
     */
    public function getPaginationStats(): array
    {
        $stats = [
            'current_page' => $this->currentPage,
            'items_count' => $this->getItemsCount(),
            'limit' => $this->limit,
            'offset' => $this->offset,
            'has_more' => $this->hasMore,
            'has_previous' => $this->hasPrevious(),
            'is_first_page' => $this->isFirstPage(),
            'is_last_page' => $this->isLastPage(),
            'is_empty' => $this->isEmpty(),
        ];

        if ($this->totalCount !== null) {
            $stats['total_count'] = $this->totalCount;
            $stats['total_pages'] = $this->totalPages;
            $stats['completion_percentage'] = $this->totalCount > 0 
                ? round((($this->offset + $this->getItemsCount()) / $this->totalCount) * 100, 2)
                : 100;
        }

        return $stats;
    }

    /**
     * 获取页码范围（用于显示分页导航）
     */
    public function getPageRange(int $maxPages = 10): array
    {
        if ($this->totalPages === null) {
            return [$this->currentPage];
        }

        $start = max(1, $this->currentPage - intdiv($maxPages, 2));
        $end = min($this->totalPages, $start + $maxPages - 1);
        
        // 调整开始位置，确保显示足够的页码
        if ($end - $start + 1 < $maxPages && $start > 1) {
            $start = max(1, $end - $maxPages + 1);
        }

        return range($start, $end);
    }

    /**
     * 创建指定页码的偏移量
     */
    public function getOffsetForPage(int $page): int
    {
        return max(0, ($page - 1) * $this->limit);
    }

    /**
     * 迭代所有数据项
     */
    public function each(callable $callback): static
    {
        foreach ($this->items as $index => $item) {
            $callback($item, $index);
        }

        return $this;
    }

    /**
     * 过滤数据项
     */
    public function filter(callable $callback): array
    {
        return array_filter($this->items, $callback);
    }

    /**
     * 映射数据项
     */
    public function map(callable $callback): array
    {
        return array_map($callback, $this->items);
    }

    /**
     * 获取第一个数据项
     */
    public function first(): mixed
    {
        return !empty($this->items) ? $this->items[0] : null;
    }

    /**
     * 获取最后一个数据项
     */
    public function last(): mixed
    {
        return !empty($this->items) ? $this->items[count($this->items) - 1] : null;
    }

    /**
     * 合并另一个分页响应的数据
     */
    public function merge(PaginatedResponse $other): static
    {
        $mergedItems = array_merge($this->items, $other->getItems());
        $newTotalCount = null;
        
        if ($this->totalCount !== null && $other->getTotalCount() !== null) {
            $newTotalCount = max($this->totalCount, $other->getTotalCount());
        } elseif ($this->totalCount !== null) {
            $newTotalCount = $this->totalCount;
        } elseif ($other->getTotalCount() !== null) {
            $newTotalCount = $other->getTotalCount();
        }

        return new static(
            items: $mergedItems,
            offset: min($this->offset, $other->getOffset()),
            limit: max($this->limit, $other->getLimit()),
            hasMore: $other->hasMore(), // 使用另一个响应的 hasMore 状态
            totalCount: $newTotalCount
        );
    }

    /**
     * 转换为数组
     */
    public function toArray(): array
    {
        return [
            'items' => $this->items,
            'pagination' => $this->getPaginationStats(),
            'navigation' => [
                'next_offset' => $this->nextOffset,
                'previous_offset' => $this->previousOffset,
                'page_range' => $this->getPageRange(),
            ]
        ];
    }

    /**
     * JSON 序列化
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    /**
     * 字符串表示
     */
    public function __toString(): string
    {
        $info = [
            "Page {$this->currentPage}",
            "{$this->getItemsCount()} items"
        ];

        if ($this->totalCount !== null) {
            $info[] = "of {$this->totalCount} total";
            $info[] = "({$this->totalPages} pages)";
        }

        if ($this->hasMore) {
            $info[] = "has more";
        }

        return implode(' - ', $info);
    }
}