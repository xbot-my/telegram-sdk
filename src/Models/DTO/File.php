<?php

declare(strict_types=1);

namespace XBot\Telegram\Models\DTO;

use XBot\Telegram\Contracts\DTOInterface;

/**
 * 文件对象
 * 
 * 表示从 Telegram 服务器下载的文件信息
 */
class File extends BaseDTO implements DTOInterface
{
    public readonly string $fileId;
    public readonly string $fileUniqueId;
    public readonly ?int $fileSize;
    public readonly ?string $filePath;

    public function __construct(
        string $fileId,
        string $fileUniqueId,
        ?int $fileSize = null,
        ?string $filePath = null
    ) {
        $this->fileId = $fileId;
        $this->fileUniqueId = $fileUniqueId;
        $this->fileSize = $fileSize;
        $this->filePath = $filePath;

        parent::__construct();
    }

    public static function fromArray(array $data): static
    {
        return new static(
            fileId: $data['file_id'] ?? '',
            fileUniqueId: $data['file_unique_id'] ?? '',
            fileSize: isset($data['file_size']) ? (int) $data['file_size'] : null,
            filePath: $data['file_path'] ?? null
        );
    }

    public function validate(): void
    {
        if (empty($this->fileId)) {
            throw new \InvalidArgumentException('File ID is required');
        }
        if (empty($this->fileUniqueId)) {
            throw new \InvalidArgumentException('File unique ID is required');
        }
    }

    public function getDownloadUrl(string $botToken): ?string
    {
        if (!$this->filePath) {
            return null;
        }
        return "https://api.telegram.org/file/bot{$botToken}/{$this->filePath}";
    }

    public function isDownloadable(): bool
    {
        return $this->filePath !== null;
    }

    public function getExtension(): ?string
    {
        if (!$this->filePath) {
            return null;
        }
        return strtolower(pathinfo($this->filePath, PATHINFO_EXTENSION)) ?: null;
    }
}