<?php

declare(strict_types=1);

namespace XBot\Telegram\Http\Response;

class PaginatedResponse extends ServerResponse
{
    /**
     * 总数量
     */
    protected ?int $total = null;

    /**
     * 当前页码
     */
    protected int $page = 1;

    /**
     * 每页数量
     */
    protected int $perPage = 100;

    /**
     * 偏移量
     */
    protected int $offset = 0;

    /**
     * 是否有更多数据
     */
    protected bool $hasMore = false;

    public function __construct(
        array   $response,
        int     $page = 1,
        int     $perPage = 100,
        int     $offset = 0,
        ?int    $total = null,
        int     $statusCode = 200,
        array   $headers = [],
        ?string $botName = null
    )
    {
        parent::__construct($response, $statusCode, $headers, $botName);

        $this->page = $page;
        $this->perPage = $perPage;
        $this->offset = $offset;
        $this->total = $total;

        // 检查是否有更多数据
        if (is_array($this->result)) {
            $this->hasMore = count($this->result) >= $this->perPage;
        }
    }

    /**
     * 获取当前页码
     */
    public function getPage(): int
    {
        return $this->page;
    }

    /**
     * 获取每页数量
     */
    public function getPerPage(): int
    {
        return $this->perPage;
    }

    /**
     * 获取偏移量
     */
    public function getOffset(): int
    {
        return $this->offset;
    }

    /**
     * 获取总数量
     */
    public function getTotal(): ?int
    {
        return $this->total;
    }

    /**
     * 检查是否有更多数据
     */
    public function hasMore(): bool
    {
        return $this->hasMore;
    }

    /**
     * 获取下一页的偏移量
     */
    public function getNextOffset(): int
    {
        return $this->offset + $this->perPage;
    }

    /**
     * 获取当前页的数据数量
     */
    public function getCount(): int
    {
        return is_array($this->result) ? count($this->result) : 0;
    }

    /**
     * 检查是否为第一页
     */
    public function isFirstPage(): bool
    {
        return $this->page === 1;
    }

    /**
     * 检查是否为最后一页
     */
    public function isLastPage(): bool
    {
        return !$this->hasMore();
    }

    /**
     * 获取分页信息
     */
    public function getPaginationInfo(): array
    {
        return [
            'page'          => $this->page,
            'per_page'      => $this->perPage,
            'offset'        => $this->offset,
            'total'         => $this->total,
            'count'         => $this->getCount(),
            'has_more'      => $this->hasMore,
            'next_offset'   => $this->getNextOffset(),
            'is_first_page' => $this->isFirstPage(),
            'is_last_page'  => $this->isLastPage(),
        ];
    }

    /**
     * 将分页响应转换为数组
     */
    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'pagination' => $this->getPaginationInfo(),
        ]);
    }
}
