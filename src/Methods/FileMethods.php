<?php

declare(strict_types=1);

namespace XBot\Telegram\Methods;
use XBot\Telegram\Models\Response\TelegramResponse;

/**
 * 文件方法组
 * 
 * 提供文件上传、下载和操作相关的 API 方法
 */
class FileMethods extends BaseMethodGroup
{
    /**
     * 获取 HTTP 客户端
     */
    public function getHttpClient(): \XBot\Telegram\Contracts\HttpClientInterface
    {
        return $this->httpClient;
    }

    /**
     * 发送照片
     */
    public function sendPhoto(
        int|string $chatId, 
        string $photo, 
        array $options = []
    ): ?array {
        $this->validateChatId($chatId);

        $parameters = array_merge([
            'chat_id' => $chatId,
            'photo' => $photo,
        ], $options);

        // 检查是否有文件需要上传
        $files = $this->extractFiles($parameters);
        $parameters = $this->prepareParameters($parameters);

        if (!empty($files)) {
            $response = $this->upload('sendPhoto', $parameters, $files);
        } else {
            $response = $this->call('sendPhoto', $parameters);
        }

        if (!$response->isOk()) {
            return null;
        }

        $result = $response->getResult();
        return is_array($result) ? $result : null;
    }

    /**
     * 发送音频
     */
    public function sendAudio(
        int|string $chatId, 
        string $audio, 
        array $options = []
    ): ?array {
        $this->validateChatId($chatId);

        $parameters = array_merge([
            'chat_id' => $chatId,
            'audio' => $audio,
        ], $options);

        $files = $this->extractFiles($parameters);
        $parameters = $this->prepareParameters($parameters);

        if (!empty($files)) {
            $response = $this->upload('sendAudio', $parameters, $files);
        } else {
            $response = $this->call('sendAudio', $parameters);
        }

        if (!$response->isOk()) {
            return null;
        }

        $result = $response->getResult();
        return is_array($result) ? $result : null;
    }

    /**
     * 发送文档
     */
    public function sendDocument(
        int|string $chatId, 
        string $document, 
        array $options = []
    ): ?array {
        $this->validateChatId($chatId);

        $parameters = array_merge([
            'chat_id' => $chatId,
            'document' => $document,
        ], $options);

        $files = $this->extractFiles($parameters);
        $parameters = $this->prepareParameters($parameters);

        if (!empty($files)) {
            $response = $this->upload('sendDocument', $parameters, $files);
        } else {
            $response = $this->call('sendDocument', $parameters);
        }

        if (!$response->isOk()) {
            return null;
        }

        $result = $response->getResult();
        return is_array($result) ? $result : null;
    }

    /**
     * 发送视频
     */
    public function sendVideo(
        int|string $chatId, 
        string $video, 
        array $options = []
    ): ?array {
        $this->validateChatId($chatId);

        $parameters = array_merge([
            'chat_id' => $chatId,
            'video' => $video,
        ], $options);

        $files = $this->extractFiles($parameters);
        $parameters = $this->prepareParameters($parameters);

        if (!empty($files)) {
            $response = $this->upload('sendVideo', $parameters, $files);
        } else {
            $response = $this->call('sendVideo', $parameters);
        }

        if (!$response->isOk()) {
            return null;
        }

        $result = $response->getResult();
        return is_array($result) ? $result : null;
    }

    /**
     * 发送动画
     */
    public function sendAnimation(
        int|string $chatId, 
        string $animation, 
        array $options = []
    ): ?array {
        $this->validateChatId($chatId);

        $parameters = array_merge([
            'chat_id' => $chatId,
            'animation' => $animation,
        ], $options);

        $files = $this->extractFiles($parameters);
        $parameters = $this->prepareParameters($parameters);

        if (!empty($files)) {
            $response = $this->upload('sendAnimation', $parameters, $files);
        } else {
            $response = $this->call('sendAnimation', $parameters);
        }

        if (!$response->isOk()) {
            return null;
        }

        $result = $response->getResult();
        return is_array($result) ? $result : null;
    }

    /**
     * 发送语音
     */
    public function sendVoice(
        int|string $chatId, 
        string $voice, 
        array $options = []
    ): ?array {
        $this->validateChatId($chatId);

        $parameters = array_merge([
            'chat_id' => $chatId,
            'voice' => $voice,
        ], $options);

        $files = $this->extractFiles($parameters);
        $parameters = $this->prepareParameters($parameters);

        if (!empty($files)) {
            $response = $this->upload('sendVoice', $parameters, $files);
        } else {
            $response = $this->call('sendVoice', $parameters);
        }

        if (!$response->isOk()) {
            return null;
        }

        $result = $response->getResult();
        return is_array($result) ? $result : null;
    }

    /**
     * 发送视频笔记
     */
    public function sendVideoNote(
        int|string $chatId, 
        string $videoNote, 
        array $options = []
    ): ?array {
        $this->validateChatId($chatId);

        $parameters = array_merge([
            'chat_id' => $chatId,
            'video_note' => $videoNote,
        ], $options);

        $files = $this->extractFiles($parameters);
        $parameters = $this->prepareParameters($parameters);

        if (!empty($files)) {
            $response = $this->upload('sendVideoNote', $parameters, $files);
        } else {
            $response = $this->call('sendVideoNote', $parameters);
        }

        if (!$response->isOk()) {
            return null;
        }

        $result = $response->getResult();
        return is_array($result) ? $result : null;
    }

    /**
     * 发送媒体组
     */
    public function sendMediaGroup(
        int|string $chatId, 
        array $media, 
        array $options = []
    ): ?array {
        $this->validateChatId($chatId);

        if (empty($media)) {
            throw new \InvalidArgumentException('Media array cannot be empty');
        }

        if (count($media) > 10) {
            throw new \InvalidArgumentException('Media group cannot contain more than 10 items');
        }

        $parameters = array_merge([
            'chat_id' => $chatId,
            'media' => json_encode($media),
        ], $options);

        // 处理媒体组中的文件上传
        $files = [];
        foreach ($media as $index => $item) {
            if (isset($item['media']) && is_string($item['media']) && $this->isFilePath($item['media'])) {
                $attachName = "attach://media{$index}";
                $files["media{$index}"] = $item['media'];
                $media[$index]['media'] = $attachName;
            }
        }

        if (!empty($files)) {
            $parameters['media'] = json_encode($media);
            $response = $this->upload('sendMediaGroup', $parameters, $files);
        } else {
            $response = $this->call('sendMediaGroup', $parameters);
        }

        if (!$response->isOk()) {
            return null;
        }

        $result = $response->getResult();
        return is_array($result) ? $result : null;
    }

    /**
     * 获取文件信息
     */
    public function getFile(string $fileId): ?array
    {
        if (empty($fileId)) {
            throw new \InvalidArgumentException('File ID cannot be empty');
        }

        $parameters = ['file_id' => $fileId];
        $response = $this->call('getFile', $parameters);

        if (!$response->isOk()) {
            return null;
        }

        $result = $response->getResult();
        return is_array($result) ? $result : null;
    }

    /**
     * 下载文件
     */
    public function downloadFile(string $fileId, ?string $savePath = null): ?string
    {
        $fileInfo = $this->getFile($fileId);

        if (!$fileInfo || !isset($fileInfo['file_path'])) {
            return null;
        }

        $filePath = $fileInfo['file_path'];
        $fileUrl = $this->httpClient->getConfig()['file_api_url'] ?? 'https://api.telegram.org/file/bot' . $this->httpClient->getToken() . '/';
        $downloadUrl = $fileUrl . $filePath;

        // 如果没有指定保存路径，生成临时文件路径
        if ($savePath === null) {
            $extension = pathinfo($filePath, PATHINFO_EXTENSION);
            $savePath = sys_get_temp_dir() . '/' . uniqid('telegram_file_') . ($extension ? '.' . $extension : '');
        }

        // 创建目录
        $directory = dirname($savePath);
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        // 下载文件
        $fileContent = file_get_contents($downloadUrl);
        if ($fileContent === false) {
            return null;
        }

        if (file_put_contents($savePath, $fileContent) === false) {
            return null;
        }

        return $savePath;
    }

    /**
     * 获取文件 URL
     */
    public function getFileUrl(string $fileId): ?string
    {
        $fileInfo = $this->getFile($fileId);

        if (!$fileInfo || !isset($fileInfo['file_path'])) {
            return null;
        }

        $filePath = $fileInfo['file_path'];
        $fileUrl = $this->httpClient->getConfig()['file_api_url'] ?? 'https://api.telegram.org/file/bot' . $this->httpClient->getToken() . '/';
        
        return $fileUrl . $filePath;
    }

    /**
     * 验证文件大小
     */
    public function validateFileSize(string $filePath, int $maxSize = 52428800): bool // 50MB 默认限制
    {
        if (!file_exists($filePath)) {
            return false;
        }

        $fileSize = filesize($filePath);
        return $fileSize !== false && $fileSize <= $maxSize;
    }

    /**
     * 获取文件 MIME 类型
     */
    public function getFileMimeType(string $filePath): ?string
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

        // 根据扩展名推断 MIME 类型
        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        $mimeTypes = [
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'webp' => 'image/webp',
            'mp4' => 'video/mp4',
            'mp3' => 'audio/mpeg',
            'ogg' => 'audio/ogg',
            'pdf' => 'application/pdf',
            'doc' => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'txt' => 'text/plain',
        ];

        return $mimeTypes[$extension] ?? null;
    }

    /**
     * 验证文件类型
     */
    public function validateFileType(string $filePath, array $allowedTypes = []): bool
    {
        if (empty($allowedTypes)) {
            return true;
        }

        $mimeType = $this->getFileMimeType($filePath);
        if (!$mimeType) {
            return false;
        }

        return in_array($mimeType, $allowedTypes, true);
    }
}
