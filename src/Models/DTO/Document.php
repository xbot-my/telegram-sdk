<?php

declare(strict_types=1);

namespace XBot\Telegram\Models\DTO;

use XBot\Telegram\Contracts\DTOInterface;

/**
 * 文档对象
 * 
 * 表示通用文件的数据传输对象
 */
class Document extends BaseDTO implements DTOInterface
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
     * 缩略图（可选）
     */
    public readonly ?PhotoSize $thumbnail;

    /**
     * 原始文件名（可选）
     */
    public readonly ?string $fileName;

    /**
     * 文档的 MIME 类型（可选）
     */
    public readonly ?string $mimeType;

    /**
     * 文件大小（字节，可选）
     */
    public readonly ?int $fileSize;

    public function __construct(
        string $fileId,
        string $fileUniqueId,
        ?PhotoSize $thumbnail = null,
        ?string $fileName = null,
        ?string $mimeType = null,
        ?int $fileSize = null
    ) {
        $this->fileId = $fileId;
        $this->fileUniqueId = $fileUniqueId;
        $this->thumbnail = $thumbnail;
        $this->fileName = $fileName;
        $this->mimeType = $mimeType;
        $this->fileSize = $fileSize;

        parent::__construct();
    }

    /**
     * 从数组创建 Document 实例
     */
    public static function fromArray(array $data): static
    {
        return new static(
            fileId: $data['file_id'] ?? '',
            fileUniqueId: $data['file_unique_id'] ?? '',
            thumbnail: isset($data['thumbnail']) && is_array($data['thumbnail']) 
                ? PhotoSize::fromArray($data['thumbnail']) 
                : null,
            fileName: $data['file_name'] ?? null,
            mimeType: $data['mime_type'] ?? null,
            fileSize: isset($data['file_size']) ? (int) $data['file_size'] : null
        );
    }

    /**
     * 验证文档数据
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
    }

    /**
     * 获取文件扩展名
     */
    public function getExtension(): ?string
    {
        if ($this->fileName) {
            return strtolower(pathinfo($this->fileName, PATHINFO_EXTENSION)) ?: null;
        }

        if ($this->mimeType) {
            return match ($this->mimeType) {
                'application/pdf' => 'pdf',
                'application/msword' => 'doc',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
                'application/vnd.ms-excel' => 'xls',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'xlsx',
                'application/vnd.ms-powerpoint' => 'ppt',
                'application/vnd.openxmlformats-officedocument.presentationml.presentation' => 'pptx',
                'application/zip' => 'zip',
                'application/x-rar-compressed' => 'rar',
                'application/x-7z-compressed' => '7z',
                'text/plain' => 'txt',
                'text/csv' => 'csv',
                'application/json' => 'json',
                'application/xml' => 'xml',
                default => null
            };
        }

        return null;
    }

    /**
     * 获取文档类型描述
     */
    public function getDocumentType(): string
    {
        $extension = $this->getExtension();

        if ($extension) {
            return match ($extension) {
                'pdf' => 'PDF Document',
                'doc', 'docx' => 'Word Document',
                'xls', 'xlsx' => 'Excel Spreadsheet',
                'ppt', 'pptx' => 'PowerPoint Presentation',
                'zip', 'rar', '7z' => 'Archive',
                'txt' => 'Text File',
                'csv' => 'CSV File',
                'json' => 'JSON File',
                'xml' => 'XML File',
                'jpg', 'jpeg', 'png', 'gif' => 'Image',
                'mp3', 'wav', 'ogg' => 'Audio File',
                'mp4', 'avi', 'mkv' => 'Video File',
                default => strtoupper($extension) . ' File'
            };
        }

        if ($this->mimeType) {
            $type = explode('/', $this->mimeType)[0];
            return match ($type) {
                'image' => 'Image',
                'audio' => 'Audio File',
                'video' => 'Video File',
                'text' => 'Text Document',
                'application' => 'Document',
                default => 'File'
            };
        }

        return 'Document';
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
     * 获取显示名称
     */
    public function getDisplayName(): string
    {
        if (!empty($this->fileName)) {
            return $this->fileName;
        }

        $type = $this->getDocumentType();
        return $type;
    }

    /**
     * 检查是否为图片文档
     */
    public function isImage(): bool
    {
        $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'];
        $extension = $this->getExtension();
        
        if ($extension && in_array($extension, $imageExtensions)) {
            return true;
        }

        return $this->mimeType && str_starts_with($this->mimeType, 'image/');
    }

    /**
     * 检查是否为文本文档
     */
    public function isText(): bool
    {
        $textExtensions = ['txt', 'csv', 'json', 'xml', 'html', 'css', 'js', 'php', 'py'];
        $extension = $this->getExtension();
        
        if ($extension && in_array($extension, $textExtensions)) {
            return true;
        }

        return $this->mimeType && str_starts_with($this->mimeType, 'text/');
    }

    /**
     * 检查是否为压缩文件
     */
    public function isArchive(): bool
    {
        $archiveExtensions = ['zip', 'rar', '7z', 'tar', 'gz', 'bz2'];
        $extension = $this->getExtension();
        
        if ($extension && in_array($extension, $archiveExtensions)) {
            return true;
        }

        if ($this->mimeType) {
            $archiveMimeTypes = [
                'application/zip',
                'application/x-rar-compressed',
                'application/x-7z-compressed',
                'application/gzip',
                'application/x-tar'
            ];
            
            return in_array($this->mimeType, $archiveMimeTypes);
        }

        return false;
    }

    /**
     * 获取完整文件信息
     */
    public function getFileInfo(): array
    {
        return [
            'name' => $this->getDisplayName(),
            'type' => $this->getDocumentType(),
            'size' => $this->getFileSizeFormatted(),
            'extension' => $this->getExtension(),
            'mime_type' => $this->mimeType,
            'is_image' => $this->isImage(),
            'is_text' => $this->isText(),
            'is_archive' => $this->isArchive(),
            'has_thumbnail' => $this->thumbnail !== null,
        ];
    }
}