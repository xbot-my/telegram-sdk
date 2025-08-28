<?php

declare(strict_types=1);

namespace XBot\Telegram\Contracts;

/**
 * 文件助手接口
 * 
 * 定义文件操作的标准接口
 */
interface FileHelperInterface
{
    /**
     * 检查文件是否存在
     */
    public function exists(string $path): bool;

    /**
     * 获取文件大小（字节）
     */
    public function size(string $path): int;

    /**
     * 获取文件 MIME 类型
     */
    public function mimeType(string $path): string;

    /**
     * 获取文件扩展名
     */
    public function extension(string $path): string;

    /**
     * 获取文件名（不含扩展名）
     */
    public function basename(string $path): string;

    /**
     * 获取文件目录
     */
    public function dirname(string $path): string;

    /**
     * 读取文件内容
     */
    public function read(string $path): string;

    /**
     * 写入文件内容
     */
    public function write(string $path, string $content): bool;

    /**
     * 复制文件
     */
    public function copy(string $source, string $destination): bool;

    /**
     * 移动文件
     */
    public function move(string $source, string $destination): bool;

    /**
     * 删除文件
     */
    public function delete(string $path): bool;

    /**
     * 创建目录
     */
    public function makeDirectory(string $path, int $mode = 0755, bool $recursive = true): bool;

    /**
     * 删除目录
     */
    public function deleteDirectory(string $path, bool $recursive = true): bool;

    /**
     * 检查是否为有效的文件路径
     */
    public function isValidPath(string $path): bool;

    /**
     * 检查是否为有效的图片文件
     */
    public function isImage(string $path): bool;

    /**
     * 检查是否为有效的视频文件
     */
    public function isVideo(string $path): bool;

    /**
     * 检查是否为有效的音频文件
     */
    public function isAudio(string $path): bool;

    /**
     * 检查是否为有效的文档文件
     */
    public function isDocument(string $path): bool;

    /**
     * 获取文件信息
     */
    public function getFileInfo(string $path): array;

    /**
     * 验证文件类型是否被允许
     */
    public function isAllowedType(string $path, array $allowedTypes = []): bool;

    /**
     * 验证文件大小是否在限制范围内
     */
    public function isValidSize(string $path, int $maxSize): bool;

    /**
     * 生成安全的文件名
     */
    public function sanitizeFilename(string $filename): string;

    /**
     * 生成唯一文件名
     */
    public function generateUniqueFilename(string $originalFilename): string;

    /**
     * 将文件转换为 Base64 编码
     */
    public function toBase64(string $path): string;

    /**
     * 从 Base64 编码创建文件
     */
    public function fromBase64(string $base64, string $path): bool;

    /**
     * 获取临时文件路径
     */
    public function getTempPath(string $prefix = 'telegram_'): string;

    /**
     * 清理临时文件
     */
    public function cleanupTempFiles(int $olderThanSeconds = 3600): int;

    /**
     * 验证上传文件
     */
    public function validateUpload(string $path, array $rules = []): array;

    /**
     * 获取文件哈希值
     */
    public function hash(string $path, string $algorithm = 'sha256'): string;
}