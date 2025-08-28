<?php

declare(strict_types=1);

namespace XBot\Telegram\Models\Response;

use XBot\Telegram\Models\DTO\PhotoSize;

/**
 * 文件响应类
 * 
 * 用于处理文件相关的 API 响应结果
 */
class FileResponse
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
     * 文件大小（字节，可选）
     */
    public readonly ?int $fileSize;

    /**
     * 文件路径（可选）
     * 使用此路径可以从 https://api.telegram.org/file/bot<token>/<file_path> 下载文件
     */
    public readonly ?string $filePath;

    /**
     * 原始文件名（可选）
     */
    public readonly ?string $fileName;

    /**
     * 文件的 MIME 类型（可选）
     */
    public readonly ?string $mimeType;

    /**
     * 文件宽度（图片/视频，可选）
     */
    public readonly ?int $width;

    /**
     * 文件高度（图片/视频，可选）
     */
    public readonly ?int $height;

    /**
     * 文件时长（音频/视频，可选）
     */
    public readonly ?int $duration;

    /**
     * 缩略图（可选）
     */
    public readonly ?PhotoSize $thumbnail;

    /**
     * 文件类型
     */
    public readonly string $fileType;

    /**
     * 下载 URL（可选）
     */
    public readonly ?string $downloadUrl;

    /**
     * 本地文件路径（如果已下载，可选）
     */
    public readonly ?string $localPath;

    public function __construct(
        string $fileId,
        string $fileUniqueId,
        string $fileType = 'unknown',
        ?int $fileSize = null,
        ?string $filePath = null,
        ?string $fileName = null,
        ?string $mimeType = null,
        ?int $width = null,
        ?int $height = null,
        ?int $duration = null,
        ?PhotoSize $thumbnail = null,
        ?string $downloadUrl = null,
        ?string $localPath = null
    ) {
        $this->fileId = $fileId;
        $this->fileUniqueId = $fileUniqueId;
        $this->fileType = $fileType;
        $this->fileSize = $fileSize;
        $this->filePath = $filePath;
        $this->fileName = $fileName;
        $this->mimeType = $mimeType;
        $this->width = $width;
        $this->height = $height;
        $this->duration = $duration;
        $this->thumbnail = $thumbnail;
        $this->downloadUrl = $downloadUrl;
        $this->localPath = $localPath;
    }

    /**
     * 从 Telegram API File 响应创建实例
     */
    public static function fromApiResponse(array $data, string $botToken, string $fileType = 'unknown'): static
    {
        $downloadUrl = null;
        if (isset($data['file_path'])) {
            $downloadUrl = "https://api.telegram.org/file/bot{$botToken}/{$data['file_path']}";
        }

        return new static(
            fileId: $data['file_id'] ?? '',
            fileUniqueId: $data['file_unique_id'] ?? '',
            fileType: $fileType,
            fileSize: isset($data['file_size']) ? (int) $data['file_size'] : null,
            filePath: $data['file_path'] ?? null,
            fileName: $data['file_name'] ?? null,
            mimeType: $data['mime_type'] ?? null,
            width: isset($data['width']) ? (int) $data['width'] : null,
            height: isset($data['height']) ? (int) $data['height'] : null,
            duration: isset($data['duration']) ? (int) $data['duration'] : null,
            thumbnail: isset($data['thumbnail']) && is_array($data['thumbnail']) 
                ? PhotoSize::fromArray($data['thumbnail']) 
                : null,
            downloadUrl: $downloadUrl
        );
    }

    /**
     * 从其他 DTO 对象创建文件响应
     */
    public static function fromDTO(object $dto, string $botToken): static
    {
        $fileType = strtolower((new \ReflectionClass($dto))->getShortName());
        $downloadUrl = null;

        // 尝试从 DTO 获取文件路径并构建下载 URL
        if (method_exists($dto, 'getFilePath') && $dto->getFilePath()) {
            $downloadUrl = "https://api.telegram.org/file/bot{$botToken}/{$dto->getFilePath()}";
        }

        return new static(
            fileId: $dto->fileId ?? '',
            fileUniqueId: $dto->fileUniqueId ?? '',
            fileType: $fileType,
            fileSize: $dto->fileSize ?? null,
            filePath: method_exists($dto, 'getFilePath') ? $dto->getFilePath() : null,
            fileName: $dto->fileName ?? null,
            mimeType: $dto->mimeType ?? null,
            width: $dto->width ?? null,
            height: $dto->height ?? null,
            duration: $dto->duration ?? null,
            thumbnail: $dto->thumbnail ?? null,
            downloadUrl: $downloadUrl
        );
    }

    /**
     * 设置本地文件路径
     */
    public function withLocalPath(string $localPath): static
    {
        return new static(
            fileId: $this->fileId,
            fileUniqueId: $this->fileUniqueId,
            fileType: $this->fileType,
            fileSize: $this->fileSize,
            filePath: $this->filePath,
            fileName: $this->fileName,
            mimeType: $this->mimeType,
            width: $this->width,
            height: $this->height,
            duration: $this->duration,
            thumbnail: $this->thumbnail,
            downloadUrl: $this->downloadUrl,
            localPath: $localPath
        );
    }

    /**
     * 验证文件响应数据
     */
    public function validate(): void
    {
        if (empty($this->fileId)) {
            throw new \InvalidArgumentException('File ID is required');
        }

        if (empty($this->fileUniqueId)) {
            throw new \InvalidArgumentException('File unique ID is required');
        }

        if ($this->fileSize !== null && $this->fileSize < 0) {
            throw new \InvalidArgumentException('File size must be non-negative');
        }

        if ($this->width !== null && $this->width <= 0) {
            throw new \InvalidArgumentException('Width must be positive');
        }

        if ($this->height !== null && $this->height <= 0) {
            throw new \InvalidArgumentException('Height must be positive');
        }

        if ($this->duration !== null && $this->duration < 0) {
            throw new \InvalidArgumentException('Duration must be non-negative');
        }
    }

    /**
     * 检查文件是否可下载
     */
    public function isDownloadable(): bool
    {
        return $this->downloadUrl !== null || $this->filePath !== null;
    }

    /**
     * 检查文件是否已下载到本地
     */
    public function isDownloaded(): bool
    {
        return $this->localPath !== null && file_exists($this->localPath);
    }

    /**
     * 获取文件扩展名
     */
    public function getExtension(): ?string
    {
        if ($this->fileName) {
            return strtolower(pathinfo($this->fileName, PATHINFO_EXTENSION)) ?: null;
        }

        if ($this->filePath) {
            return strtolower(pathinfo($this->filePath, PATHINFO_EXTENSION)) ?: null;
        }

        if ($this->mimeType) {
            return match ($this->mimeType) {
                'image/jpeg' => 'jpg',
                'image/png' => 'png',
                'image/gif' => 'gif',
                'image/webp' => 'webp',
                'video/mp4' => 'mp4',
                'video/avi' => 'avi',
                'audio/mpeg' => 'mp3',
                'audio/ogg' => 'ogg',
                'application/pdf' => 'pdf',
                default => null
            };
        }

        return null;
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
     * 获取文件类型描述
     */
    public function getFileTypeDescription(): string
    {
        return match ($this->fileType) {
            'photo', 'photosize' => 'Photo',
            'document' => 'Document',
            'video' => 'Video',
            'audio' => 'Audio',
            'voice' => 'Voice Message',
            'video_note' => 'Video Note',
            'animation' => 'Animation/GIF',
            'sticker' => 'Sticker',
            default => ucfirst($this->fileType)
        };
    }

    /**
     * 检查是否为图片
     */
    public function isImage(): bool
    {
        if (in_array($this->fileType, ['photo', 'photosize'])) {
            return true;
        }

        if ($this->mimeType && str_starts_with($this->mimeType, 'image/')) {
            return true;
        }

        $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'];
        $extension = $this->getExtension();
        
        return $extension && in_array($extension, $imageExtensions);
    }

    /**
     * 检查是否为视频
     */
    public function isVideo(): bool
    {
        if (in_array($this->fileType, ['video', 'video_note', 'animation'])) {
            return true;
        }

        if ($this->mimeType && str_starts_with($this->mimeType, 'video/')) {
            return true;
        }

        $videoExtensions = ['mp4', 'avi', 'mkv', 'mov', 'wmv', 'flv'];
        $extension = $this->getExtension();
        
        return $extension && in_array($extension, $videoExtensions);
    }

    /**
     * 检查是否为音频
     */
    public function isAudio(): bool
    {
        if (in_array($this->fileType, ['audio', 'voice'])) {
            return true;
        }

        if ($this->mimeType && str_starts_with($this->mimeType, 'audio/')) {
            return true;
        }

        $audioExtensions = ['mp3', 'wav', 'ogg', 'flac', 'aac'];
        $extension = $this->getExtension();
        
        return $extension && in_array($extension, $audioExtensions);
    }

    /**
     * 获取显示名称
     */
    public function getDisplayName(): string
    {
        if (!empty($this->fileName)) {
            return $this->fileName;
        }

        $extension = $this->getExtension();
        $type = $this->getFileTypeDescription();
        
        if ($extension) {
            return "{$type}.{$extension}";
        }

        return $type;
    }

    /**
     * 获取分辨率字符串（如果是图片或视频）
     */
    public function getResolution(): ?string
    {
        if ($this->width && $this->height) {
            return "{$this->width}×{$this->height}";
        }

        return null;
    }

    /**
     * 获取时长格式化字符串（如果是音频或视频）
     */
    public function getDurationFormatted(): ?string
    {
        if ($this->duration === null) {
            return null;
        }

        $hours = intdiv($this->duration, 3600);
        $minutes = intdiv($this->duration % 3600, 60);
        $seconds = $this->duration % 60;

        if ($hours > 0) {
            return sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
        }

        return sprintf('%02d:%02d', $minutes, $seconds);
    }

    /**
     * 获取完整文件信息
     */
    public function getFileInfo(): array
    {
        return [
            'file_id' => $this->fileId,
            'file_unique_id' => $this->fileUniqueId,
            'file_type' => $this->fileType,
            'display_name' => $this->getDisplayName(),
            'file_size' => $this->getFileSizeFormatted(),
            'file_size_bytes' => $this->fileSize,
            'mime_type' => $this->mimeType,
            'extension' => $this->getExtension(),
            'is_image' => $this->isImage(),
            'is_video' => $this->isVideo(),
            'is_audio' => $this->isAudio(),
            'resolution' => $this->getResolution(),
            'duration' => $this->getDurationFormatted(),
            'duration_seconds' => $this->duration,
            'has_thumbnail' => $this->thumbnail !== null,
            'is_downloadable' => $this->isDownloadable(),
            'is_downloaded' => $this->isDownloaded(),
            'download_url' => $this->downloadUrl,
            'local_path' => $this->localPath,
        ];
    }

    /**
     * 转换为数组
     */
    public function toArray(): array
    {
        return [
            'file_id' => $this->fileId,
            'file_unique_id' => $this->fileUniqueId,
            'file_type' => $this->fileType,
            'file_size' => $this->fileSize,
            'file_path' => $this->filePath,
            'file_name' => $this->fileName,
            'mime_type' => $this->mimeType,
            'width' => $this->width,
            'height' => $this->height,
            'duration' => $this->duration,
            'thumbnail' => $this->thumbnail?->toArray(),
            'download_url' => $this->downloadUrl,
            'local_path' => $this->localPath,
            'info' => $this->getFileInfo(),
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
        $info = [$this->getDisplayName()];

        if ($this->fileSize !== null) {
            $info[] = $this->getFileSizeFormatted();
        }

        if ($this->getResolution()) {
            $info[] = $this->getResolution();
        }

        if ($this->getDurationFormatted()) {
            $info[] = $this->getDurationFormatted();
        }

        return implode(' - ', $info);
    }
}