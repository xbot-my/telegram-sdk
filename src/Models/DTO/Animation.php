<?php

declare(strict_types=1);

namespace XBot\Telegram\Models\DTO;

use XBot\Telegram\Contracts\DTOInterface;

/**
 * 动画对象
 * 
 * 表示 GIF 或 H.264/MPEG-4 AVC 视频（无声音）
 */
class Animation extends BaseDTO implements DTOInterface
{
    public readonly string $fileId;
    public readonly string $fileUniqueId;
    public readonly int $width;
    public readonly int $height;
    public readonly int $duration;
    public readonly ?PhotoSize $thumbnail;
    public readonly ?string $fileName;
    public readonly ?string $mimeType;
    public readonly ?int $fileSize;

    public function __construct(
        string $fileId,
        string $fileUniqueId,
        int $width,
        int $height,
        int $duration,
        ?PhotoSize $thumbnail = null,
        ?string $fileName = null,
        ?string $mimeType = null,
        ?int $fileSize = null
    ) {
        $this->fileId = $fileId;
        $this->fileUniqueId = $fileUniqueId;
        $this->width = $width;
        $this->height = $height;
        $this->duration = $duration;
        $this->thumbnail = $thumbnail;
        $this->fileName = $fileName;
        $this->mimeType = $mimeType;
        $this->fileSize = $fileSize;

        parent::__construct();
    }

    public static function fromArray(array $data): static
    {
        return new static(
            fileId: $data['file_id'] ?? '',
            fileUniqueId: $data['file_unique_id'] ?? '',
            width: (int) ($data['width'] ?? 0),
            height: (int) ($data['height'] ?? 0),
            duration: (int) ($data['duration'] ?? 0),
            thumbnail: isset($data['thumbnail']) && is_array($data['thumbnail']) 
                ? PhotoSize::fromArray($data['thumbnail']) 
                : null,
            fileName: $data['file_name'] ?? null,
            mimeType: $data['mime_type'] ?? null,
            fileSize: isset($data['file_size']) ? (int) $data['file_size'] : null
        );
    }

    public function validate(): void
    {
        if (empty($this->fileId)) {
            throw new \InvalidArgumentException('File ID is required');
        }
        if ($this->width <= 0 || $this->height <= 0) {
            throw new \InvalidArgumentException('Width and height must be positive');
        }
    }

    public function getAspectRatio(): float
    {
        return $this->width / $this->height;
    }

    public function getDurationFormatted(): string
    {
        $minutes = intdiv($this->duration, 60);
        $seconds = $this->duration % 60;
        return sprintf('%02d:%02d', $minutes, $seconds);
    }
}