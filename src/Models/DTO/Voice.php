<?php

declare(strict_types=1);

namespace XBot\Telegram\Models\DTO;

use XBot\Telegram\Contracts\DTOInterface;

/**
 * 语音对象
 * 
 * 表示语音消息的数据传输对象
 */
class Voice extends BaseDTO implements DTOInterface
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
     * 语音时长（秒）
     */
    public readonly int $duration;

    /**
     * 语音文件的 MIME 类型（可选）
     */
    public readonly ?string $mimeType;

    /**
     * 文件大小（字节，可选）
     */
    public readonly ?int $fileSize;

    public function __construct(
        string $fileId,
        string $fileUniqueId,
        int $duration,
        ?string $mimeType = null,
        ?int $fileSize = null
    ) {
        $this->fileId = $fileId;
        $this->fileUniqueId = $fileUniqueId;
        $this->duration = $duration;
        $this->mimeType = $mimeType;
        $this->fileSize = $fileSize;

        parent::__construct();
    }

    /**
     * 从数组创建 Voice 实例
     */
    public static function fromArray(array $data): static
    {
        return new static(
            fileId: $data['file_id'] ?? '',
            fileUniqueId: $data['file_unique_id'] ?? '',
            duration: (int) ($data['duration'] ?? 0),
            mimeType: $data['mime_type'] ?? null,
            fileSize: isset($data['file_size']) ? (int) $data['file_size'] : null
        );
    }

    /**
     * 验证语音数据
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

        $units = ['B', 'KB', 'MB'];
        $size = $this->fileSize;
        $unitIndex = 0;

        while ($size >= 1024 && $unitIndex < count($units) - 1) {
            $size /= 1024;
            $unitIndex++;
        }

        return sprintf('%.1f %s', $size, $units[$unitIndex]);
    }

    /**
     * 获取语音格式
     */
    public function getVoiceFormat(): string
    {
        if ($this->mimeType) {
            return match ($this->mimeType) {
                'audio/ogg' => 'OGG',
                'audio/mpeg' => 'MP3',
                'audio/mp4' => 'M4A',
                'audio/wav' => 'WAV',
                'audio/webm' => 'WebM',
                default => strtoupper(explode('/', $this->mimeType)[1] ?? 'UNKNOWN')
            };
        }

        return 'OGG'; // Telegram 默认格式
    }

    /**
     * 计算估算比特率
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

        if ($bitrate >= 1000) {
            return sprintf('%.0f kbps', $bitrate / 1000);
        }

        return "{$bitrate} bps";
    }

    /**
     * 检查是否为短语音消息
     */
    public function isShortMessage(): bool
    {
        return $this->duration <= 5; // 5秒以内
    }

    /**
     * 检查是否为长语音消息
     */
    public function isLongMessage(): bool
    {
        return $this->duration >= 60; // 1分钟以上
    }

    /**
     * 获取语音质量评级
     */
    public function getQualityRating(): string
    {
        $bitrate = $this->getEstimatedBitrate();
        
        if ($bitrate === null) {
            return 'Unknown';
        }
        
        return match (true) {
            $bitrate >= 64000 => 'High',      // 64 kbps+
            $bitrate >= 32000 => 'Medium',    // 32 kbps+
            $bitrate >= 16000 => 'Standard',  // 16 kbps+
            default => 'Low'
        };
    }

    /**
     * 检查是否适合语音识别
     */
    public function isSuitableForSpeechRecognition(): bool
    {
        // 基于时长和估算质量判断
        if ($this->duration < 1 || $this->duration > 300) { // 1秒到5分钟
            return false;
        }

        $bitrate = $this->getEstimatedBitrate();
        return $bitrate === null || $bitrate >= 16000; // 至少 16 kbps
    }

    /**
     * 计算播放进度百分比
     */
    public function getPlaybackProgress(int $currentTime): float
    {
        if ($this->duration <= 0) {
            return 0.0;
        }

        $progress = ($currentTime / $this->duration) * 100;
        return min(100.0, max(0.0, $progress));
    }

    /**
     * 获取剩余播放时间
     */
    public function getRemainingTime(int $currentTime): int
    {
        return max(0, $this->duration - $currentTime);
    }

    /**
     * 获取格式化的剩余时间
     */
    public function getRemainingTimeFormatted(int $currentTime): string
    {
        $remaining = $this->getRemainingTime($currentTime);
        $minutes = intdiv($remaining, 60);
        $seconds = $remaining % 60;
        
        return sprintf('%02d:%02d', $minutes, $seconds);
    }

    /**
     * 获取语音消息描述
     */
    public function getDescription(): string
    {
        $parts = [
            'Voice Message',
            $this->getDurationFormatted(),
        ];

        if ($this->fileSize !== null) {
            $parts[] = $this->getFileSizeFormatted();
        }

        $parts[] = $this->getVoiceFormat();

        return implode(' - ', $parts);
    }

    /**
     * 获取完整语音信息
     */
    public function getVoiceInfo(): array
    {
        return [
            'duration' => $this->getDurationFormatted(),
            'duration_seconds' => $this->duration,
            'format' => $this->getVoiceFormat(),
            'file_size' => $this->getFileSizeFormatted(),
            'file_size_bytes' => $this->fileSize,
            'bitrate' => $this->getBitrateFormatted(),
            'quality' => $this->getQualityRating(),
            'is_short' => $this->isShortMessage(),
            'is_long' => $this->isLongMessage(),
            'suitable_for_speech_recognition' => $this->isSuitableForSpeechRecognition(),
            'mime_type' => $this->mimeType,
        ];
    }

    /**
     * 字符串表示
     */
    public function __toString(): string
    {
        return $this->getDescription();
    }
}