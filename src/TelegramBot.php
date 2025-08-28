<?php

declare(strict_types=1);

namespace XBot\Telegram;

use XBot\Telegram\Contracts\HttpClientInterface;
use XBot\Telegram\Contracts\TelegramBotInterface;
use XBot\Telegram\Models\DTO\Message;
use XBot\Telegram\Models\DTO\Update;
use XBot\Telegram\Models\DTO\User;
use XBot\Telegram\Models\Response\TelegramResponse;
use XBot\Telegram\Exceptions\ValidationException;
use XBot\Telegram\Exceptions\ConfigurationException;

/**
 * Telegram Bot 实例类
 * 
 * 单个 Bot 实例的核心实现，提供完整的 Telegram Bot API 封装
 */
class TelegramBot implements TelegramBotInterface
{
    /**
     * Bot 实例名称
     */
    protected string $name;

    /**
     * HTTP 客户端
     */
    protected HttpClientInterface $httpClient;

    /**
     * Bot 配置
     */
    protected array $config;

    /**
     * Bot 信息缓存
     */
    protected ?User $botInfo = null;

    /**
     * 实例创建时间
     */
    protected int $createdAt;

    /**
     * 实例统计信息
     */
    protected array $stats = [
        'total_calls' => 0,
        'successful_calls' => 0,
        'failed_calls' => 0,
        'last_call_time' => null,
        'uptime' => 0,
    ];

    public function __construct(
        string $name,
        HttpClientInterface $httpClient,
        array $config = []
    ) {
        $this->name = $name;
        $this->httpClient = $httpClient;
        $this->config = $config;
        $this->createdAt = time();

        $this->validateConfiguration();
    }

    /**
     * 验证配置
     */
    protected function validateConfiguration(): void
    {
        if (empty($this->name)) {
            throw ConfigurationException::invalid('name', $this->name, 'Bot name cannot be empty');
        }

        $token = $this->httpClient->getToken();
        if (empty($token)) {
            throw ConfigurationException::missingBotToken($this->name);
        }

        if (!preg_match('/^\d{8,10}:[a-zA-Z0-9_-]{35}$/', $token)) {
            throw ConfigurationException::invalidBotToken($token, $this->name);
        }
    }

    /**
     * 获取 Bot 名称
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * 获取 Bot Token
     */
    public function getToken(): string
    {
        return $this->httpClient->getToken();
    }

    /**
     * 获取 Bot 信息
     */
    public function getMe(): User
    {
        if ($this->botInfo === null) {
            $response = $this->call('getMe');
            $this->botInfo = $response->toDTO(User::class);
        }

        return $this->botInfo;
    }

    /**
     * 发送消息
     */
    public function sendMessage(
        int|string $chatId,
        string $text,
        array $options = []
    ): Message {
        $this->validateChatId($chatId);
        $this->validateMessageText($text);

        $parameters = array_merge([
            'chat_id' => $chatId,
            'text' => $text,
        ], $options);

        $response = $this->call('sendMessage', $parameters);
        return $response->toDTO(Message::class);
    }

    /**
     * 编辑消息文本
     */
    public function editMessageText(
        int|string $chatId,
        int $messageId,
        string $text,
        array $options = []
    ): Message {
        $this->validateChatId($chatId);
        $this->validateMessageId($messageId);
        $this->validateMessageText($text);

        $parameters = array_merge([
            'chat_id' => $chatId,
            'message_id' => $messageId,
            'text' => $text,
        ], $options);

        $response = $this->call('editMessageText', $parameters);
        return $response->toDTO(Message::class);
    }

    /**
     * 删除消息
     */
    public function deleteMessage(
        int|string $chatId,
        int $messageId
    ): bool {
        $this->validateChatId($chatId);
        $this->validateMessageId($messageId);

        $parameters = [
            'chat_id' => $chatId,
            'message_id' => $messageId,
        ];

        $response = $this->call('deleteMessage', $parameters);
        return (bool) $response->getResult();
    }

    /**
     * 转发消息
     */
    public function forwardMessage(
        int|string $chatId,
        int|string $fromChatId,
        int $messageId,
        array $options = []
    ): Message {
        $this->validateChatId($chatId);
        $this->validateChatId($fromChatId);
        $this->validateMessageId($messageId);

        $parameters = array_merge([
            'chat_id' => $chatId,
            'from_chat_id' => $fromChatId,
            'message_id' => $messageId,
        ], $options);

        $response = $this->call('forwardMessage', $parameters);
        return $response->toDTO(Message::class);
    }

    /**
     * 复制消息
     */
    public function copyMessage(
        int|string $chatId,
        int|string $fromChatId,
        int $messageId,
        array $options = []
    ): int {
        $this->validateChatId($chatId);
        $this->validateChatId($fromChatId);
        $this->validateMessageId($messageId);

        $parameters = array_merge([
            'chat_id' => $chatId,
            'from_chat_id' => $fromChatId,
            'message_id' => $messageId,
        ], $options);

        $response = $this->call('copyMessage', $parameters);
        return (int) $response->getResult()['message_id'];
    }

    /**
     * 获取更新
     */
    public function getUpdates(array $options = []): array
    {
        $response = $this->call('getUpdates', $options);
        return $response->toDTOArray(Update::class);
    }

    /**
     * 设置 Webhook
     */
    public function setWebhook(string $url, array $options = []): bool
    {
        $this->validateWebhookUrl($url);

        $parameters = array_merge([
            'url' => $url,
        ], $options);

        // If a local certificate file is provided, use upload
        if (isset($options['certificate']) && is_string($options['certificate'])) {
            $files = $this->extractFiles(['certificate' => $options['certificate']]);
            if (!empty($files)) {
                $response = $this->httpClient->upload('setWebhook', $parameters, $files);
                return (bool) $response->getResult();
            }
        }

        $response = $this->call('setWebhook', $parameters);
        return (bool) $response->getResult();
    }

    /**
     * 删除 Webhook
     */
    public function deleteWebhook(bool $dropPendingUpdates = false): bool
    {
        $parameters = [
            'drop_pending_updates' => $dropPendingUpdates,
        ];

        $response = $this->call('deleteWebhook', $parameters);
        return (bool) $response->getResult();
    }

    /**
     * 获取 Webhook 信息
     */
    public function getWebhookInfo(): array
    {
        $response = $this->call('getWebhookInfo');
        return $response->getResult();
    }

    /**
     * 发送照片
     */
    public function sendPhoto(
        int|string $chatId,
        string $photo,
        array $options = []
    ): Message {
        return $this->sendMedia('sendPhoto', $chatId, 'photo', $photo, $options);
    }

    /**
     * 发送视频
     */
    public function sendVideo(
        int|string $chatId,
        string $video,
        array $options = []
    ): Message {
        return $this->sendMedia('sendVideo', $chatId, 'video', $video, $options);
    }

    /**
     * 发送音频
     */
    public function sendAudio(
        int|string $chatId,
        string $audio,
        array $options = []
    ): Message {
        return $this->sendMedia('sendAudio', $chatId, 'audio', $audio, $options);
    }

    /**
     * 发送文档
     */
    public function sendDocument(
        int|string $chatId,
        string $document,
        array $options = []
    ): Message {
        return $this->sendMedia('sendDocument', $chatId, 'document', $document, $options);
    }

    /**
     * 发送贴纸
     */
    public function sendSticker(
        int|string $chatId,
        string $sticker,
        array $options = []
    ): Message {
        return $this->sendMedia('sendSticker', $chatId, 'sticker', $sticker, $options);
    }

    /**
     * 发送动画
     */
    public function sendAnimation(
        int|string $chatId,
        string $animation,
        array $options = []
    ): Message {
        return $this->sendMedia('sendAnimation', $chatId, 'animation', $animation, $options);
    }

    /**
     * 发送语音
     */
    public function sendVoice(
        int|string $chatId,
        string $voice,
        array $options = []
    ): Message {
        return $this->sendMedia('sendVoice', $chatId, 'voice', $voice, $options);
    }

    /**
     * 发送位置
     */
    public function sendLocation(
        int|string $chatId,
        float $latitude,
        float $longitude,
        array $options = []
    ): Message {
        $this->validateChatId($chatId);
        $this->validateLocation($latitude, $longitude);

        $parameters = array_merge([
            'chat_id' => $chatId,
            'latitude' => $latitude,
            'longitude' => $longitude,
        ], $options);

        $response = $this->call('sendLocation', $parameters);
        return $response->toDTO(Message::class);
    }

    /**
     * 发送联系人
     */
    public function sendContact(
        int|string $chatId,
        string $phoneNumber,
        string $firstName,
        array $options = []
    ): Message {
        $this->validateChatId($chatId);

        if (empty($phoneNumber)) {
            throw ValidationException::required('phoneNumber');
        }

        if (empty($firstName)) {
            throw ValidationException::required('firstName');
        }

        $parameters = array_merge([
            'chat_id' => $chatId,
            'phone_number' => $phoneNumber,
            'first_name' => $firstName,
        ], $options);

        $response = $this->call('sendContact', $parameters);
        return $response->toDTO(Message::class);
    }

    /**
     * 发送投票
     */
    public function sendPoll(
        int|string $chatId,
        string $question,
        array $options,
        array $settings = []
    ): Message {
        $this->validateChatId($chatId);

        if (empty($question)) {
            throw ValidationException::required('question');
        }

        if (empty($options) || count($options) < 2) {
            throw ValidationException::invalidRange('options', 2, 10, count($options));
        }

        $parameters = array_merge([
            'chat_id' => $chatId,
            'question' => $question,
            'options' => $options,
        ], $settings);

        $response = $this->call('sendPoll', $parameters);
        return $response->toDTO(Message::class);
    }

    /**
     * 获取聊天信息
     */
    public function getChat(int|string $chatId): array
    {
        $this->validateChatId($chatId);

        $parameters = [
            'chat_id' => $chatId,
        ];

        $response = $this->call('getChat', $parameters);
        return $response->getResult();
    }

    /**
     * 获取聊天成员
     */
    public function getChatMember(int|string $chatId, int $userId): array
    {
        $this->validateChatId($chatId);
        $this->validateUserId($userId);

        $parameters = [
            'chat_id' => $chatId,
            'user_id' => $userId,
        ];

        $response = $this->call('getChatMember', $parameters);
        return $response->getResult();
    }

    /**
     * 获取聊天成员数量
     */
    public function getChatMemberCount(int|string $chatId): int
    {
        $this->validateChatId($chatId);

        $parameters = [
            'chat_id' => $chatId,
        ];

        $response = $this->call('getChatMemberCount', $parameters);
        return (int) $response->getResult();
    }

    /**
     * 获取聊天管理员
     */
    public function getChatAdministrators(int|string $chatId): array
    {
        $this->validateChatId($chatId);

        $parameters = [
            'chat_id' => $chatId,
        ];

        $response = $this->call('getChatAdministrators', $parameters);
        return $response->getResult();
    }

    /**
     * 设置聊天照片
     */
    public function setChatPhoto(int|string $chatId, string $photo): bool
    {
        $this->validateChatId($chatId);

        $parameters = [
            'chat_id' => $chatId,
        ];

        $files = $this->extractFiles(['photo' => $photo]);
        if (!empty($files)) {
            $response = $this->httpClient->upload('setChatPhoto', $parameters, $files);
        } else {
            $parameters['photo'] = $photo;
            $response = $this->call('setChatPhoto', $parameters);
        }

        return (bool) $response->getResult();
    }

    /**
     * 删除聊天照片
     */
    public function deleteChatPhoto(int|string $chatId): bool
    {
        $this->validateChatId($chatId);

        $parameters = [
            'chat_id' => $chatId,
        ];

        $response = $this->call('deleteChatPhoto', $parameters);
        return (bool) $response->getResult();
    }

    /**
     * 设置聊天标题
     */
    public function setChatTitle(int|string $chatId, string $title): bool
    {
        $this->validateChatId($chatId);

        if ($title === '') {
            throw ValidationException::required('title');
        }
        if (strlen($title) > 128) {
            throw ValidationException::invalidLength('title', 0, 128, $title);
        }

        $parameters = [
            'chat_id' => $chatId,
            'title' => $title,
        ];

        $response = $this->call('setChatTitle', $parameters);
        return (bool) $response->getResult();
    }

    /**
     * 设置聊天描述
     */
    public function setChatDescription(int|string $chatId, string $description = ''): bool
    {
        $this->validateChatId($chatId);

        if (strlen($description) > 255) {
            throw ValidationException::invalidLength('description', 0, 255, $description);
        }

        $parameters = [
            'chat_id' => $chatId,
            'description' => $description,
        ];

        $response = $this->call('setChatDescription', $parameters);
        return (bool) $response->getResult();
    }

    /**
     * 固定聊天消息
     */
    public function pinChatMessage(int|string $chatId, int $messageId, bool $disableNotification = false): bool
    {
        $this->validateChatId($chatId);
        $this->validateMessageId($messageId);

        $parameters = [
            'chat_id' => $chatId,
            'message_id' => $messageId,
            'disable_notification' => $disableNotification,
        ];

        $response = $this->call('pinChatMessage', $parameters);
        return (bool) $response->getResult();
    }

    /**
     * 取消固定聊天消息
     */
    public function unpinChatMessage(int|string $chatId, int $messageId = null): bool
    {
        $this->validateChatId($chatId);
        if ($messageId !== null) {
            $this->validateMessageId($messageId);
        }

        $parameters = [
            'chat_id' => $chatId,
            'message_id' => $messageId,
        ];

        $response = $this->call('unpinChatMessage', $parameters);
        return (bool) $response->getResult();
    }

    /**
     * 取消固定所有聊天消息
     */
    public function unpinAllChatMessages(int|string $chatId): bool
    {
        $this->validateChatId($chatId);

        $parameters = [
            'chat_id' => $chatId,
        ];

        $response = $this->call('unpinAllChatMessages', $parameters);
        return (bool) $response->getResult();
    }

    /**
     * 离开聊天
     */
    public function leaveChat(int|string $chatId): bool
    {
        $this->validateChatId($chatId);

        $parameters = [
            'chat_id' => $chatId,
        ];

        $response = $this->call('leaveChat', $parameters);
        return (bool) $response->getResult();
    }

    /**
     * 封禁聊天成员
     */
    public function banChatMember(
        int|string $chatId,
        int $userId,
        array $options = []
    ): bool {
        $this->validateChatId($chatId);
        $this->validateUserId($userId);

        $parameters = array_merge([
            'chat_id' => $chatId,
            'user_id' => $userId,
        ], $options);

        $response = $this->call('banChatMember', $parameters);
        return (bool) $response->getResult();
    }

    /**
     * 解封聊天成员
     */
    public function unbanChatMember(
        int|string $chatId,
        int $userId,
        array $options = []
    ): bool {
        $this->validateChatId($chatId);
        $this->validateUserId($userId);

        $parameters = array_merge([
            'chat_id' => $chatId,
            'user_id' => $userId,
        ], $options);

        $response = $this->call('unbanChatMember', $parameters);
        return (bool) $response->getResult();
    }

    /**
     * 限制聊天成员
     */
    public function restrictChatMember(
        int|string $chatId,
        int $userId,
        array $permissions,
        array $options = []
    ): bool {
        $this->validateChatId($chatId);
        $this->validateUserId($userId);

        $parameters = array_merge([
            'chat_id' => $chatId,
            'user_id' => $userId,
            'permissions' => $permissions,
        ], $options);

        $response = $this->call('restrictChatMember', $parameters);
        return (bool) $response->getResult();
    }

    /**
     * 提升聊天成员
     */
    public function promoteChatMember(
        int|string $chatId,
        int $userId,
        array $options = []
    ): bool {
        $this->validateChatId($chatId);
        $this->validateUserId($userId);

        $parameters = array_merge([
            'chat_id' => $chatId,
            'user_id' => $userId,
        ], $options);

        $response = $this->call('promoteChatMember', $parameters);
        return (bool) $response->getResult();
    }

    /**
     * 回答回调查询
     */
    public function answerCallbackQuery(
        string $callbackQueryId,
        array $options = []
    ): bool {
        if (empty($callbackQueryId)) {
            throw ValidationException::required('callbackQueryId');
        }

        $parameters = array_merge([
            'callback_query_id' => $callbackQueryId,
        ], $options);

        $response = $this->call('answerCallbackQuery', $parameters);
        return (bool) $response->getResult();
    }

    /**
     * 回答内联查询
     */
    public function answerInlineQuery(
        string $inlineQueryId,
        array $results,
        array $options = []
    ): bool {
        if (empty($inlineQueryId)) {
            throw ValidationException::required('inlineQueryId');
        }

        $parameters = array_merge([
            'inline_query_id' => $inlineQueryId,
            'results' => $results,
        ], $options);

        $response = $this->call('answerInlineQuery', $parameters);
        return (bool) $response->getResult();
    }

    /**
     * 执行原始 API 调用
     */
    public function call(string $method, array $parameters = []): TelegramResponse
    {
        $this->stats['total_calls']++;
        $this->stats['last_call_time'] = time();

        try {
            $response = $this->httpClient->post($method, $parameters);
            $response->ensureOk();
            
            $this->stats['successful_calls']++;
            return $response;

        } catch (\Throwable $e) {
            $this->stats['failed_calls']++;
            throw $e;
        }
    }

    /**
     * 检查 Bot 是否健康
     */
    public function healthCheck(): bool
    {
        try {
            $this->getMe();
            return true;
        } catch (\Throwable) {
            return false;
        }
    }

    /**
     * 获取 Bot 统计信息
     */
    public function getStats(): array
    {
        $uptime = time() - $this->createdAt;
        
        return array_merge($this->stats, [
            'name' => $this->name,
            'token' => substr($this->getToken(), 0, 10) . '...',
            'created_at' => $this->createdAt,
            'uptime' => $uptime,
            'uptime_formatted' => $this->formatUptime($uptime),
            'success_rate' => $this->stats['total_calls'] > 0 
                ? ($this->stats['successful_calls'] / $this->stats['total_calls']) * 100 
                : 0,
            'http_client_stats' => $this->httpClient->getStats() ?? [],
        ]);
    }

    /**
     * 格式化运行时间
     */
    protected function formatUptime(int $seconds): string
    {
        $days = floor($seconds / 86400);
        $hours = floor(($seconds % 86400) / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        $secs = $seconds % 60;

        return sprintf('%dd %02dh %02dm %02ds', $days, $hours, $minutes, $secs);
    }

    /**
     * 提取文件参数
     */
    protected function extractFiles(array $parameters): array
    {
        $files = [];

        foreach ($parameters as $key => $value) {
            if (is_string($value) && $this->isFilePath($value)) {
                $files[$key] = $value;
            }
        }

        return $files;
    }

    /**
     * 检查是否为文件路径
     */
    protected function isFilePath(string $value): bool
    {
        // 检查是否为本地文件路径
        if (file_exists($value)) {
            return true;
        }

        // 检查是否为资源
        if (is_resource($value)) {
            return true;
        }

        // 如果是 URL 或文件 ID，则不是文件路径
        if (filter_var($value, FILTER_VALIDATE_URL) || preg_match('/^[A-Za-z0-9_-]+$/', $value)) {
            return false;
        }

        return false;
    }

    /**
     * 验证聊天 ID
     */
    protected function validateChatId(int|string $chatId): void
    {
        if (empty($chatId)) {
            throw ValidationException::required('chatId');
        }

        if (is_string($chatId)) {
            // Accept: @username, numeric IDs including negatives (e.g., -1001234567890)
            if (!str_starts_with($chatId, '@') && !preg_match('/^-?\d+$/', $chatId)) {
                throw ValidationException::invalidFormat('chatId', 'numeric ID or @username', $chatId);
            }
        }
    }

    /**
     * 验证消息 ID
     */
    protected function validateMessageId(int $messageId): void
    {
        if ($messageId <= 0) {
            throw ValidationException::invalidType('messageId', 'positive integer', $messageId);
        }
    }

    /**
     * 验证用户 ID
     */
    protected function validateUserId(int $userId): void
    {
        if ($userId <= 0) {
            throw ValidationException::invalidType('userId', 'positive integer', $userId);
        }
    }

    /**
     * 验证消息文本
     */
    protected function validateMessageText(string $text): void
    {
        if (empty($text)) {
            throw ValidationException::required('text');
        }

        if (strlen($text) > 4096) {
            throw ValidationException::invalidLength('text', 0, 4096, $text);
        }
    }

    /**
     * 验证 Webhook URL
     */
    protected function validateWebhookUrl(string $url): void
    {
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            throw ValidationException::invalidFormat('url', 'valid URL', $url);
        }

        if (!str_starts_with($url, 'https://')) {
            throw ValidationException::invalidFormat('url', 'HTTPS URL', $url);
        }
    }

    /**
     * 验证位置坐标
     */
    protected function validateLocation(float $latitude, float $longitude): void
    {
        if ($latitude < -90 || $latitude > 90) {
            throw ValidationException::invalidRange('latitude', -90, 90, $latitude);
        }

        if ($longitude < -180 || $longitude > 180) {
            throw ValidationException::invalidRange('longitude', -180, 180, $longitude);
        }
    }

    /**
     * 统一的媒体发送助手
     */
    protected function sendMedia(
        string $method,
        int|string $chatId,
        string $mediaParam,
        string $mediaValue,
        array $options = []
    ): Message {
        $this->validateChatId($chatId);

        $parameters = array_merge([
            'chat_id' => $chatId,
            $mediaParam => $mediaValue,
        ], $options);

        $files = $this->extractFiles([$mediaParam => $mediaValue]);

        if (!empty($files)) {
            $response = $this->httpClient->upload($method, $parameters, $files);
        } else {
            $response = $this->call($method, $parameters);
        }

        return $response->toDTO(Message::class);
    }
}
