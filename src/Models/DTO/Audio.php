<?php

declare(strict_types=1);

namespace XBot\Telegram\Models\DTO;

use XBot\Telegram\Contracts\DTOInterface;

/**
 * 音频文件对象
 * 
 * 表示音频文件的数据传输对象
 */
class Audio extends BaseDTO implements DTOInterface
{
    /**
     * 文件的唯一标识符
     */
    public readonly string $fileId;

    /**
     * 文件的唯一标识符，对于同一个文件来说是永远不变的
     */
    public readonly string $fileUniqueId;

    /**
     * 音频时长（秒）
     */
    public readonly int $duration;

    /**
     * 音频演奏者（可选）
     */
    public readonly ?string $performer;

    /**
     * 音频标题（可选）
     */
    public readonly ?string $title;

    /**
     * 原始文件名（可选）
     */
    public readonly ?string $fileName;

    /**
     * 音频文件的 MIME 类型（可选）
     */
    public readonly ?string $mimeType;

    /**
     * 文件大小（字节，可选）
     */
    public readonly ?int $fileSize;

    /**
     * 音频缩略图（可选）
     */
    public readonly ?PhotoSize $thumbnail;

    public function __construct(
        string $fileId,
        string $fileUniqueId,
        int $duration,
        ?string $performer = null,
        ?string $title = null,
        ?string $fileName = null,
        ?string $mimeType = null,
        ?int $fileSize = null,
        ?PhotoSize $thumbnail = null
    ) {
        $this->fileId = $fileId;
        $this->fileUniqueId = $fileUniqueId;
        $this->duration = $duration;
        $this->performer = $performer;
        $this->title = $title;
        $this->fileName = $fileName;
        $this->mimeType = $mimeType;
        $this->fileSize = $fileSize;
        $this->thumbnail = $thumbnail;

        parent::__construct();
    }

    /**
     * 从数组创建 Audio 实例
     */
    public static function fromArray(array $data): static
    {
        return new static(
            fileId: $data['file_id'] ?? '',
            fileUniqueId: $data['file_unique_id'] ?? '',
            duration: (int) ($data['duration'] ?? 0),
            performer: $data['performer'] ?? null,
            title: $data['title'] ?? null,
            fileName: $data['file_name'] ?? null,
            mimeType: $data['mime_type'] ?? null,
            fileSize: isset($data['file_size']) ? (int) $data['file_size'] : null,
            thumbnail: isset($data['thumbnail']) && is_array($data['thumbnail']) 
                ? PhotoSize::fromArray($data['thumbnail']) 
                : null
        );
    }

    /**
     * 验证音频数据
     */
    public function validate(): void
    {
        if (empty($this->fileId)) {
            throw new \InvalidArgumentException('File ID is required');
        }

        if (empty($this->fileUniqueId)) {
            throw new \InvalidArgumentException('File unique ID is required');
        }

        if ($this->duration <= 0) {
            throw new \InvalidArgumentException('Duration must be positive');
        }

        if ($this->fileSize !== null && $this->fileSize < 0) {
            throw new \InvalidArgumentException('File size must be non-negative');
        }
    }

    /**
     * 检查是否为音乐文件
     */
    public function isMusic(): bool
    {
        return !empty($this->performer) || !empty($this->title);
    }

    /**
     * 获取格式化的时长
     */
    public function getDurationFormatted(): string
    {
        $minutes = intdiv($this->duration, 60);
        $seconds = $this->duration % 60;
        
        return sprintf('%02d:%02d', $minutes, $seconds);
    }

    /**
     * 获取人类可读的文件大小
     */
    public function getFileSizeFormatted(): ?string
    {
        if ($this->fileSize === null) {
            return null;
        }

        $units = ['B', 'KB', 'MB', 'GB'];
        $size = $this->fileSize;
        $unitIndex = 0;

        while ($size >= 1024 && $unitIndex < count($units) - 1) {
            $size /= 1024;
            $unitIndex++;
        }

        return sprintf('%.1f %s', $size, $units[$unitIndex]);
    }

    /**
     * 获取显示标题
     */
    public function getDisplayTitle(): string
    {
        if (!empty($this->title)) {
            return $this->title;
        }

        if (!empty($this->fileName)) {
            return pathinfo($this->fileName, PATHINFO_FILENAME);
        }

        return 'Audio File';
    }

    /**
     * 获取完整描述
     */
    public function getFullDescription(): string
    {
        $parts = [];

        if (!empty($this->title)) {
            $parts[] = $this->title;
        }

        if (!empty($this->performer)) {
            $parts[] = "by {$this->performer}";
        }

        $parts[] = $this->getDurationFormatted();

        if ($this->fileSize !== null) {
            $parts[] = $this->getFileSizeFormatted();
        }

        return implode(' - ', $parts);
    }

    /**
     * 检查音频格式
     */
    public function getAudioFormat(): ?string
    {
        if ($this->mimeType) {
            return match ($this->mimeType) {
                'audio/mpeg' => 'MP3',
                'audio/ogg' => 'OGG',
                'audio/wav' => 'WAV',
                'audio/flac' => 'FLAC',
                'audio/aac' => 'AAC',
                'audio/mp4' => 'M4A',
                default => strtoupper(explode('/', $this->mimeType)[1] ?? 'UNKNOWN')
            };
        }

        if ($this->fileName) {
            $extension = strtoupper(pathinfo($this->fileName, PATHINFO_EXTENSION));
            return $extension ?: null;
        }

        return null;
    }
}