<?php

declare(strict_types=1);

namespace XBot\Telegram\Models\DTO;

use XBot\Telegram\Contracts\DTOInterface;

/**
 * 图片尺寸对象
 * 
 * 表示一张照片或文件缩略图的尺寸信息
 */
class PhotoSize extends BaseDTO implements DTOInterface
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
     * 图片宽度
     */
    public readonly int $width;

    /**
     * 图片高度
     */
    public readonly int $height;

    /**
     * 文件大小（字节，可选）
     */
    public readonly ?int $fileSize;

    public function __construct(
        string $fileId,
        string $fileUniqueId,
        int $width,
        int $height,
        ?int $fileSize = null
    ) {
        $this->fileId = $fileId;
        $this->fileUniqueId = $fileUniqueId;
        $this->width = $width;
        $this->height = $height;
        $this->fileSize = $fileSize;

        parent::__construct();
    }

    /**
     * 从数组创建 PhotoSize 实例
     */
    public static function fromArray(array $data): static
    {
        return new static(
            fileId: $data['file_id'] ?? '',
            fileUniqueId: $data['file_unique_id'] ?? '',
            width: (int) ($data['width'] ?? 0),
            height: (int) ($data['height'] ?? 0),
            fileSize: isset($data['file_size']) ? (int) $data['file_size'] : null
        );
    }

    /**
     * 验证图片尺寸数据
     */
    public function validate(): void
    {
        if (empty($this->fileId)) {
            throw new \InvalidArgumentException('File ID is required');
        }

        if (empty($this->fileUniqueId)) {
            throw new \InvalidArgumentException('File unique ID is required');
        }

        if ($this->width <= 0) {
            throw new \InvalidArgumentException('Width must be positive');
        }

        if ($this->height <= 0) {
            throw new \InvalidArgumentException('Height must be positive');
        }

        if ($this->fileSize !== null && $this->fileSize < 0) {
            throw new \InvalidArgumentException('File size must be non-negative');
        }
    }

    /**
     * 计算宽高比
     */
    public function getAspectRatio(): float
    {
        return $this->width / $this->height;
    }

    /**
     * 获取图片分辨率描述
     */
    public function getResolutionString(): string
    {
        return "{$this->width}×{$this->height}";
    }

    /**
     * 计算总像素数
     */
    public function getTotalPixels(): int
    {
        return $this->width * $this->height;
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
     * 获取图片质量等级
     */
    public function getQualityLevel(): string
    {
        $pixels = $this->getTotalPixels();
        
        return match (true) {
            $pixels >= 8000000 => 'Ultra High', // 8MP+
            $pixels >= 3000000 => 'High',       // 3MP+
            $pixels >= 1000000 => 'Medium',     // 1MP+
            $pixels >= 500000 => 'Standard',    // 0.5MP+
            default => 'Low'
        };
    }

    /**
     * 判断是否为横向图片
     */
    public function isLandscape(): bool
    {
        return $this->width > $this->height;
    }

    /**
     * 判断是否为纵向图片
     */
    public function isPortrait(): bool
    {
        return $this->height > $this->width;
    }

    /**
     * 判断是否为正方形图片
     */
    public function isSquare(): bool
    {
        return $this->width === $this->height;
    }

    /**
     * 获取图片方向
     */
    public function getOrientation(): string
    {
        return match (true) {
            $this->isLandscape() => 'landscape',
            $this->isPortrait() => 'portrait',
            default => 'square'
        };
    }

    /**
     * 计算缩放后的尺寸（保持宽高比）
     */
    public function getScaledSize(int $maxWidth, int $maxHeight): array
    {
        $ratio = $this->getAspectRatio();
        
        if ($this->width <= $maxWidth && $this->height <= $maxHeight) {
            return ['width' => $this->width, 'height' => $this->height];
        }

        if ($this->isLandscape()) {
            $newWidth = min($maxWidth, $this->width);
            $newHeight = (int) round($newWidth / $ratio);
            
            if ($newHeight > $maxHeight) {
                $newHeight = $maxHeight;
                $newWidth = (int) round($newHeight * $ratio);
            }
        } else {
            $newHeight = min($maxHeight, $this->height);
            $newWidth = (int) round($newHeight * $ratio);
            
            if ($newWidth > $maxWidth) {
                $newWidth = $maxWidth;
                $newHeight = (int) round($newWidth / $ratio);
            }
        }

        return ['width' => $newWidth, 'height' => $newHeight];
    }

    /**
     * 比较两个图片尺寸的大小
     */
    public function compareTo(PhotoSize $other): int
    {
        $thisPixels = $this->getTotalPixels();
        $otherPixels = $other->getTotalPixels();
        
        return $thisPixels <=> $otherPixels;
    }

    /**
     * 检查是否适合作为缩略图
     */
    public function isSuitableForThumbnail(int $maxSize = 320): bool
    {
        return $this->width <= $maxSize && $this->height <= $maxSize;
    }

    /**
     * 获取完整的尺寸信息
     */
    public function getDimensionInfo(): array
    {
        return [
            'width' => $this->width,
            'height' => $this->height,
            'resolution' => $this->getResolutionString(),
            'pixels' => $this->getTotalPixels(),
            'aspect_ratio' => $this->getAspectRatio(),
            'orientation' => $this->getOrientation(),
            'quality_level' => $this->getQualityLevel(),
            'file_size' => $this->getFileSizeFormatted(),
            'is_thumbnail_suitable' => $this->isSuitableForThumbnail(),
        ];
    }

    /**
     * 字符串表示
     */
    public function __toString(): string
    {
        $info = [$this->getResolutionString()];
        
        if ($this->fileSize !== null) {
            $info[] = $this->getFileSizeFormatted();
        }
        
        $info[] = $this->getOrientation();
        
        return implode(' - ', $info);
    }
}