<?php

declare(strict_types=1);

namespace XBot\Telegram\Utils;

/**
 * 文件操作工具类
 * 
 * 提供文件处理相关的实用方法
 */
class FileHelper
{
    /**
     * 支持的图片格式
     */
    public const IMAGE_FORMATS = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp'];

    /**
     * 支持的视频格式
     */
    public const VIDEO_FORMATS = ['mp4', 'avi', 'mkv', 'mov', 'wmv', 'flv', 'webm'];

    /**
     * 支持的音频格式
     */
    public const AUDIO_FORMATS = ['mp3', 'wav', 'ogg', 'flac', 'aac', 'm4a'];

    /**
     * 获取文件大小（字节）
     */
    public static function getFileSize(string $filePath): ?int
    {
        if (!file_exists($filePath)) {
            return null;
        }

        $size = filesize($filePath);
        return $size !== false ? $size : null;
    }

    /**
     * 获取人类可读的文件大小
     */
    public static function formatFileSize(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        $bytes /= (1 << (10 * $pow));

        return round($bytes, 2) . ' ' . $units[$pow];
    }

    /**
     * 获取文件扩展名
     */
    public static function getExtension(string $filePath): ?string
    {
        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        return $extension ?: null;
    }

    /**
     * 获取文件 MIME 类型
     */
    public static function getMimeType(string $filePath): ?string
    {
        if (!file_exists($filePath)) {
            return null;
        }

        if (function_exists('mime_content_type')) {
            return mime_content_type($filePath);
        }

        if (function_exists('finfo_open')) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $filePath);
            finfo_close($finfo);
            return $mimeType ?: null;
        }

        return self::getMimeTypeByExtension($filePath);
    }

    /**
     * 根据扩展名推断 MIME 类型
     */
    public static function getMimeTypeByExtension(string $filePath): ?string
    {
        $extension = self::getExtension($filePath);
        if (!$extension) {
            return null;
        }

        $mimeTypes = [
            // Images
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'webp' => 'image/webp',
            'bmp' => 'image/bmp',
            
            // Videos
            'mp4' => 'video/mp4',
            'avi' => 'video/x-msvideo',
            'mkv' => 'video/x-matroska',
            'mov' => 'video/quicktime',
            'wmv' => 'video/x-ms-wmv',
            'flv' => 'video/x-flv',
            'webm' => 'video/webm',
            
            // Audio
            'mp3' => 'audio/mpeg',
            'wav' => 'audio/wav',
            'ogg' => 'audio/ogg',
            'flac' => 'audio/flac',
            'aac' => 'audio/aac',
            'm4a' => 'audio/mp4',
            
            // Documents
            'pdf' => 'application/pdf',
            'doc' => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'xls' => 'application/vnd.ms-excel',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'ppt' => 'application/vnd.ms-powerpoint',
            'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'txt' => 'text/plain',
            'csv' => 'text/csv',
            'json' => 'application/json',
            'xml' => 'application/xml',
            'zip' => 'application/zip',
            'rar' => 'application/x-rar-compressed',
            '7z' => 'application/x-7z-compressed',
        ];

        return $mimeTypes[$extension] ?? null;
    }

    /**
     * 检查文件是否为图片
     */
    public static function isImage(string $filePath): bool
    {
        $extension = self::getExtension($filePath);
        return $extension && in_array($extension, self::IMAGE_FORMATS);
    }

    /**
     * 检查文件是否为视频
     */
    public static function isVideo(string $filePath): bool
    {
        $extension = self::getExtension($filePath);
        return $extension && in_array($extension, self::VIDEO_FORMATS);
    }

    /**
     * 检查文件是否为音频
     */
    public static function isAudio(string $filePath): bool
    {
        $extension = self::getExtension($filePath);
        return $extension && in_array($extension, self::AUDIO_FORMATS);
    }

    /**
     * 验证文件大小限制
     */
    public static function validateFileSize(string $filePath, int $maxSize): bool
    {
        $fileSize = self::getFileSize($filePath);
        return $fileSize !== null && $fileSize <= $maxSize;
    }

    /**
     * 创建安全的文件名
     */
    public static function sanitizeFileName(string $fileName): string
    {
        // 移除危险字符
        $fileName = preg_replace('/[^a-zA-Z0-9._-]/', '_', $fileName);
        
        // 限制长度
        if (strlen($fileName) > 255) {
            $extension = self::getExtension($fileName);
            $baseName = pathinfo($fileName, PATHINFO_FILENAME);
            $maxLength = $extension ? 250 : 255;
            
            $fileName = substr($baseName, 0, $maxLength);
            if ($extension) {
                $fileName .= '.' . $extension;
            }
        }

        return $fileName;
    }

    /**
     * 生成唯一文件名
     */
    public static function generateUniqueFileName(string $originalName, string $directory = ''): string
    {
        $extension = self::getExtension($originalName);
        $baseName = pathinfo($originalName, PATHINFO_FILENAME);
        $baseName = self::sanitizeFileName($baseName);
        
        $fileName = $baseName;
        if ($extension) {
            $fileName .= '.' . $extension;
        }

        $fullPath = $directory ? rtrim($directory, '/') . '/' . $fileName : $fileName;
        
        if (!file_exists($fullPath)) {
            return $fileName;
        }

        $counter = 1;
        do {
            $newFileName = $baseName . '_' . $counter;
            if ($extension) {
                $newFileName .= '.' . $extension;
            }
            
            $fullPath = $directory ? rtrim($directory, '/') . '/' . $newFileName : $newFileName;
            $counter++;
        } while (file_exists($fullPath));

        return $newFileName;
    }

    /**
     * 创建目录（如果不存在）
     */
    public static function ensureDirectory(string $directory): bool
    {
        if (is_dir($directory)) {
            return true;
        }

        return mkdir($directory, 0755, true);
    }

    /**
     * 安全删除文件
     */
    public static function deleteFile(string $filePath): bool
    {
        if (!file_exists($filePath)) {
            return true;
        }

        return unlink($filePath);
    }

    /**
     * 复制文件到指定目录
     */
    public static function copyFile(string $source, string $destination): bool
    {
        if (!file_exists($source)) {
            return false;
        }

        $destinationDir = dirname($destination);
        if (!self::ensureDirectory($destinationDir)) {
            return false;
        }

        return copy($source, $destination);
    }

    /**
     * 移动文件到指定目录
     */
    public static function moveFile(string $source, string $destination): bool
    {
        if (!file_exists($source)) {
            return false;
        }

        $destinationDir = dirname($destination);
        if (!self::ensureDirectory($destinationDir)) {
            return false;
        }

        return rename($source, $destination);
    }

    /**
     * 获取临时文件路径
     */
    public static function getTempFilePath(string $prefix = 'telegram_', string $extension = ''): string
    {
        $tempDir = sys_get_temp_dir();
        $fileName = $prefix . uniqid();
        
        if ($extension) {
            $fileName .= '.' . ltrim($extension, '.');
        }

        return $tempDir . DIRECTORY_SEPARATOR . $fileName;
    }

    /**
     * 读取文件内容
     */
    public static function readFile(string $filePath): ?string
    {
        if (!file_exists($filePath)) {
            return null;
        }

        $content = file_get_contents($filePath);
        return $content !== false ? $content : null;
    }

    /**
     * 写入文件内容
     */
    public static function writeFile(string $filePath, string $content): bool
    {
        $directory = dirname($filePath);
        if (!self::ensureDirectory($directory)) {
            return false;
        }

        return file_put_contents($filePath, $content) !== false;
    }
}