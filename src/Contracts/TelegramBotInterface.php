<?php

declare(strict_types=1);

namespace XBot\Telegram\Contracts;

use XBot\Telegram\Models\DTO\Update;
use XBot\Telegram\Models\DTO\Message;
use XBot\Telegram\Models\DTO\User;
use XBot\Telegram\Models\Response\TelegramResponse;

/**
 * Telegram Bot 接口
 * 
 * 定义单个 Bot 实例的标准接口
 */
interface TelegramBotInterface
{
    /**
     * 获取 Bot 名称
     */
    public function getName(): string;

    /**
     * 获取 Bot Token
     */
    public function getToken(): string;

    /**
     * 获取 Bot 信息
     */
    public function getMe(): User;

    /**
     * 发送消息
     */
    public function sendMessage(
        int|string $chatId,
        string $text,
        array $options = []
    ): Message;

    /**
     * 编辑消息文本
     */
    public function editMessageText(
        int|string $chatId,
        int $messageId,
        string $text,
        array $options = []
    ): Message;

    /**
     * 删除消息
     */
    public function deleteMessage(
        int|string $chatId,
        int $messageId
    ): bool;

    /**
     * 转发消息
     */
    public function forwardMessage(
        int|string $chatId,
        int|string $fromChatId,
        int $messageId,
        array $options = []
    ): Message;

    /**
     * 复制消息
     */
    public function copyMessage(
        int|string $chatId,
        int|string $fromChatId,
        int $messageId,
        array $options = []
    ): int;

    /**
     * 获取更新
     */
    public function getUpdates(array $options = []): array;

    /**
     * 设置 Webhook
     */
    public function setWebhook(string $url, array $options = []): bool;

    /**
     * 删除 Webhook
     */
    public function deleteWebhook(bool $dropPendingUpdates = false): bool;

    /**
     * 获取 Webhook 信息
     */
    public function getWebhookInfo(): array;

    /**
     * 发送照片
     */
    public function sendPhoto(
        int|string $chatId,
        string $photo,
        array $options = []
    ): Message;

    /**
     * 发送视频
     */
    public function sendVideo(
        int|string $chatId,
        string $video,
        array $options = []
    ): Message;

    /**
     * 发送音频
     */
    public function sendAudio(
        int|string $chatId,
        string $audio,
        array $options = []
    ): Message;

    /**
     * 发送文档
     */
    public function sendDocument(
        int|string $chatId,
        string $document,
        array $options = []
    ): Message;

    /**
     * 发送贴纸
     */
    public function sendSticker(
        int|string $chatId,
        string $sticker,
        array $options = []
    ): Message;

    /**
     * 发送动画
     */
    public function sendAnimation(
        int|string $chatId,
        string $animation,
        array $options = []
    ): Message;

    /**
     * 发送语音
     */
    public function sendVoice(
        int|string $chatId,
        string $voice,
        array $options = []
    ): Message;

    /**
     * 发送位置
     */
    public function sendLocation(
        int|string $chatId,
        float $latitude,
        float $longitude,
        array $options = []
    ): Message;

    /**
     * 发送联系人
     */
    public function sendContact(
        int|string $chatId,
        string $phoneNumber,
        string $firstName,
        array $options = []
    ): Message;

    /**
     * 发送投票
     */
    public function sendPoll(
        int|string $chatId,
        string $question,
        array $options,
        array $settings = []
    ): Message;

    /**
     * 获取聊天信息
     */
    public function getChat(int|string $chatId): array;

    /**
     * 获取聊天成员
     */
    public function getChatMember(int|string $chatId, int $userId): array;

    /**
     * 获取聊天成员数量
     */
    public function getChatMemberCount(int|string $chatId): int;

    /**
     * 封禁聊天成员
     */
    public function banChatMember(
        int|string $chatId,
        int $userId,
        array $options = []
    ): bool;

    /**
     * 解封聊天成员
     */
    public function unbanChatMember(
        int|string $chatId,
        int $userId,
        array $options = []
    ): bool;

    /**
     * 限制聊天成员
     */
    public function restrictChatMember(
        int|string $chatId,
        int $userId,
        array $permissions,
        array $options = []
    ): bool;

    /**
     * 提升聊天成员
     */
    public function promoteChatMember(
        int|string $chatId,
        int $userId,
        array $options = []
    ): bool;

    /**
     * 回答回调查询
     */
    public function answerCallbackQuery(
        string $callbackQueryId,
        array $options = []
    ): bool;

    /**
     * 回答内联查询
     */
    public function answerInlineQuery(
        string $inlineQueryId,
        array $results,
        array $options = []
    ): bool;

    /**
     * 执行原始 API 调用
     */
    public function call(string $method, array $parameters = []): TelegramResponse;

    /**
     * 检查 Bot 是否健康
     */
    public function healthCheck(): bool;

    /**
     * 获取 Bot 统计信息
     */
    public function getStats(): array;
}