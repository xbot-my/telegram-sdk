<?php

declare(strict_types=1);

namespace XBot\Telegram\Methods;

use XBot\Telegram\Contracts\MethodGroupInterface;
use XBot\Telegram\Models\Response\TelegramResponse;

/**
 * 游戏方法组
 * 
 * 提供游戏相关的 API 方法
 */
class GameMethods extends BaseMethodGroup implements MethodGroupInterface
{
    /**
     * 获取 HTTP 客户端
     */
    public function getHttpClient(): \XBot\Telegram\Contracts\HttpClientInterface
    {
        return $this->httpClient;
    }

    /**
     * 发送游戏
     */
    public function sendGame(
        int|string $chatId,
        string $gameShortName,
        array $options = []
    ): ?array {
        $this->validateChatId($chatId);

        if (empty($gameShortName)) {
            throw new \InvalidArgumentException('Game short name cannot be empty');
        }

        $parameters = array_merge([
            'chat_id' => $chatId,
            'game_short_name' => $gameShortName,
        ], $options);

        $response = $this->call('sendGame', $parameters);

        if (!$response->isOk()) {
            return null;
        }

        $result = $response->getResult();
        return is_array($result) ? $result : null;
    }

    /**
     * 设置游戏分数
     */
    public function setGameScore(
        int $userId,
        int $score,
        array $options = []
    ): bool|array {
        $this->validateUserId($userId);

        if ($score < 0) {
            throw new \InvalidArgumentException('Score must be non-negative');
        }

        $parameters = array_merge([
            'user_id' => $userId,
            'score' => $score,
        ], $options);

        $response = $this->call('setGameScore', $parameters);

        if (!$response->isOk()) {
            return false;
        }

        $result = $response->getResult();
        
        // 如果返回 true，表示分数更新成功但消息没有变化
        if ($result === true) {
            return true;
        }

        // 如果返回消息对象，返回数组格式
        return is_array($result) ? $result : false;
    }

    /**
     * 获取游戏高分榜
     */
    public function getGameHighScores(
        int $userId,
        array $options = []
    ): ?array {
        $this->validateUserId($userId);

        $parameters = array_merge([
            'user_id' => $userId,
        ], $options);

        $response = $this->call('getGameHighScores', $parameters);

        if (!$response->isOk()) {
            return null;
        }

        $result = $response->getResult();
        return is_array($result) ? $result : null;
    }

    /**
     * 为特定聊天消息设置游戏分数
     */
    public function setGameScoreForMessage(
        int|string $chatId,
        int $messageId,
        int $userId,
        int $score,
        array $options = []
    ): bool|array {
        $this->validateChatId($chatId);
        $this->validateMessageId($messageId);
        $this->validateUserId($userId);

        if ($score < 0) {
            throw new \InvalidArgumentException('Score must be non-negative');
        }

        $parameters = array_merge([
            'chat_id' => $chatId,
            'message_id' => $messageId,
            'user_id' => $userId,
            'score' => $score,
        ], $options);

        return $this->setGameScore($userId, $score, $parameters);
    }

    /**
     * 为内联消息设置游戏分数
     */
    public function setGameScoreForInlineMessage(
        string $inlineMessageId,
        int $userId,
        int $score,
        array $options = []
    ): bool|array {
        if (empty($inlineMessageId)) {
            throw new \InvalidArgumentException('Inline message ID cannot be empty');
        }

        $this->validateUserId($userId);

        if ($score < 0) {
            throw new \InvalidArgumentException('Score must be non-negative');
        }

        $parameters = array_merge([
            'inline_message_id' => $inlineMessageId,
            'user_id' => $userId,
            'score' => $score,
        ], $options);

        return $this->setGameScore($userId, $score, $parameters);
    }

    /**
     * 获取特定聊天消息的游戏高分榜
     */
    public function getGameHighScoresForMessage(
        int|string $chatId,
        int $messageId,
        int $userId
    ): ?array {
        $this->validateChatId($chatId);
        $this->validateMessageId($messageId);
        $this->validateUserId($userId);

        $parameters = [
            'chat_id' => $chatId,
            'message_id' => $messageId,
            'user_id' => $userId,
        ];

        return $this->getGameHighScores($userId, $parameters);
    }

    /**
     * 获取内联消息的游戏高分榜
     */
    public function getGameHighScoresForInlineMessage(
        string $inlineMessageId,
        int $userId
    ): ?array {
        if (empty($inlineMessageId)) {
            throw new \InvalidArgumentException('Inline message ID cannot be empty');
        }

        $this->validateUserId($userId);

        $parameters = [
            'inline_message_id' => $inlineMessageId,
            'user_id' => $userId,
        ];

        return $this->getGameHighScores($userId, $parameters);
    }

    /**
     * 创建游戏内联键盘按钮
     */
    public function createGameButton(string $text, string $callbackGame = ''): array
    {
        if (empty($text)) {
            throw new \InvalidArgumentException('Button text cannot be empty');
        }

        return [
            'text' => $text,
            'callback_game' => $callbackGame,
        ];
    }

    /**
     * 验证分数更新选项
     */
    protected function validateScoreOptions(array $options): void
    {
        if (isset($options['force']) && !is_bool($options['force'])) {
            throw new \InvalidArgumentException('Force option must be boolean');
        }

        if (isset($options['disable_edit_message']) && !is_bool($options['disable_edit_message'])) {
            throw new \InvalidArgumentException('Disable edit message option must be boolean');
        }
    }
}