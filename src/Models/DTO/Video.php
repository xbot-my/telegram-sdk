<?php

declare(strict_types=1);

namespace XBot\Telegram\Models\DTO;

use XBot\Telegram\Contracts\DTOInterface;

/**
 * 视频对象
 * 
 * 表示视频文件的数据传输对象
 */
class Video extends BaseDTO implements DTOInterface
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
     * 视频宽度
     */
    public readonly int $width;

    /**
     * 视频高度
     */
    public readonly int $height;

    /**
     * 视频时长（秒）
     */
    public readonly int $duration;

    /**
     * 视频缩略图（可选）
     */
    public readonly ?PhotoSize $thumbnail;

    /**
     * 原始文件名（可选）
     */
    public readonly ?string $fileName;

    /**
     * 视频文件的 MIME 类型（可选）
     */
    public readonly ?string $mimeType;

    /**
     * 文件大小（字节，可选）
     */
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

    /**
     * 从数组创建 Video 实例
     */
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

    /**
     * 验证视频数据
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

        if ($this->duration < 0) {
            throw new \InvalidArgumentException('Duration must be non-negative');
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
     * 获取视频分辨率描述
     */
    public function getResolutionString(): string
    {
        return "{$this->width}×{$this->height}";
    }

    /**
     * 获取格式化的时长
     */
    public function getDurationFormatted(): string
    {
        $hours = intdiv($this->duration, 3600);
        $minutes = intdiv($this->duration % 3600, 60);
        $seconds = $this->duration % 60;

        if ($hours > 0) {
            return sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
        }

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
     * 获取视频质量等级
     */
    public function getQualityLevel(): string
    {
        $pixels = $this->width * $this->height;
        
        return match (true) {
            $pixels >= 8294400 => '4K',      // 3840×2160
            $pixels >= 2073600 => '2K',      // 1920×1080
            $pixels >= 921600 => 'HD',       // 1280×720
            $pixels >= 307200 => 'SD',       // 640×480
            default => 'Low'
        };
    }

    /**
     * 判断是否为横向视频
     */
    public function isLandscape(): bool
    {
        return $this->width > $this->height;
    }

    /**
     * 判断是否为纵向视频
     */
    public function isPortrait(): bool
    {
        return $this->height > $this->width;
    }

    /**
     * 判断是否为正方形视频
     */
    public function isSquare(): bool
    {
        return $this->width === $this->height;
    }

    /**
     * 获取视频方向
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
     * 获取视频格式
     */
    public function getVideoFormat(): ?string
    {
        if ($this->mimeType) {
            return match ($this->mimeType) {
                'video/mp4' => 'MP4',
                'video/avi' => 'AVI',
                'video/mov' => 'MOV',
                'video/mkv' => 'MKV',
                'video/webm' => 'WebM',
                'video/flv' => 'FLV',
                'video/wmv' => 'WMV',
                'video/3gp' => '3GP',
                default => strtoupper(explode('/', $this->mimeType)[1] ?? 'UNKNOWN')
            };
        }

        if ($this->fileName) {
            $extension = strtoupper(pathinfo($this->fileName, PATHINFO_EXTENSION));
            return $extension ?: null;
        }

        return null;
    }

    /**
     * 计算比特率（如果有文件大小）
     */
    public function getEstimatedBitrate(): ?int
    {
        if ($this->fileSize === null || $this->duration <= 0) {
            return null;
        }

        // 计算比特率 (bits per second)
        return (int) (($this->fileSize * 8) / $this->duration);
    }

    /**
     * 获取格式化的比特率
     */
    public function getBitrateFormatted(): ?string
    {
        $bitrate = $this->getEstimatedBitrate();
        
        if ($bitrate === null) {
            return null;
        }

        if ($bitrate >= 1000000) {
            return sprintf('%.1f Mbps', $bitrate / 1000000);
        }

        if ($bitrate >= 1000) {
            return sprintf('%.0f kbps', $bitrate / 1000);
        }

        return "{$bitrate} bps";
    }

    /**
     * 检查是否适合在移动设备播放
     */
    public function isMobileFriendly(): bool
    {
        // 基于文件大小和分辨率判断
        if ($this->fileSize && $this->fileSize > 50 * 1024 * 1024) { // 50MB
            return false;
        }

        $pixels = $this->width * $this->height;
        return $pixels <= 1920 * 1080; // 1080p 以下
    }

    /**
     * 检查是否为短视频
     */
    public function isShortVideo(): bool
    {
        return $this->duration <= 60; // 1分钟以内
    }

    /**
     * 检查是否为长视频
     */
    public function isLongVideo(): bool
    {
        return $this->duration >= 600; // 10分钟以上
    }

    /**
     * 获取显示名称
     */
    public function getDisplayName(): string
    {
        if (!empty($this->fileName)) {
            return pathinfo($this->fileName, PATHINFO_FILENAME);
        }

        return "Video ({$this->getQualityLevel()})";
    }

    /**
     * 获取完整视频信息
     */
    public function getVideoInfo(): array
    {
        return [
            'name' => $this->getDisplayName(),
            'resolution' => $this->getResolutionString(),
            'duration' => $this->getDurationFormatted(),
            'duration_seconds' => $this->duration,
            'quality' => $this->getQualityLevel(),
            'orientation' => $this->getOrientation(),
            'format' => $this->getVideoFormat(),
            'file_size' => $this->getFileSizeFormatted(),
            'file_size_bytes' => $this->fileSize,
            'bitrate' => $this->getBitrateFormatted(),
            'aspect_ratio' => $this->getAspectRatio(),
            'has_thumbnail' => $this->thumbnail !== null,
            'is_mobile_friendly' => $this->isMobileFriendly(),
            'is_short_video' => $this->isShortVideo(),
            'is_long_video' => $this->isLongVideo(),
        ];
    }

    /**
     * 字符串表示
     */
    public function __toString(): string
    {
        $info = [
            $this->getDisplayName(),
            $this->getResolutionString(),
            $this->getDurationFormatted()
        ];
        
        if ($this->fileSize !== null) {
            $info[] = $this->getFileSizeFormatted();
        }

        $format = $this->getVideoFormat();
        if ($format) {
            $info[] = $format;
        }
        
        return implode(' - ', $info);
    }
}