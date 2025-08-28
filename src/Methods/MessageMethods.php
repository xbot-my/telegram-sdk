<?php

declare(strict_types=1);

namespace XBot\Telegram\Methods;

use XBot\Telegram\Models\DTO\Message;
use XBot\Telegram\Models\Response\TelegramResponse;

/**
 * 消息相关的 API 方法
 * 
 * 包含发送、编辑、删除、转发消息等相关功能
 */
class MessageMethods extends BaseMethodGroup
{
    /**
     * 发送文本消息
     */
    public function sendMessage(
        int|string $chatId,
        string $text,
        array $options = []
    ): Message {
        $this->validateChatId($chatId);
        $this->validateTextLength($text, 4096);

        $parameters = $this->prepareParameters(array_merge([
            'chat_id' => $chatId,
            'text' => $text,
        ], $options));

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
        $this->validateTextLength($text, 4096);

        $parameters = $this->prepareParameters(array_merge([
            'chat_id' => $chatId,
            'message_id' => $messageId,
            'text' => $text,
        ], $options));

        $response = $this->call('editMessageText', $parameters);
        return $response->toDTO(Message::class);
    }

    /**
     * 编辑内联消息文本
     */
    public function editInlineMessageText(
        string $inlineMessageId,
        string $text,
        array $options = []
    ): bool {
        $this->validateTextLength($text, 4096);

        $parameters = $this->prepareParameters(array_merge([
            'inline_message_id' => $inlineMessageId,
            'text' => $text,
        ], $options));

        $response = $this->call('editMessageText', $parameters);
        return (bool) $response->getResult();
    }

    /**
     * 编辑消息媒体
     */
    public function editMessageMedia(
        int|string $chatId,
        int $messageId,
        array $media,
        array $options = []
    ): Message {
        $this->validateChatId($chatId);
        $this->validateMessageId($messageId);

        $parameters = array_merge([
            'chat_id' => $chatId,
            'message_id' => $messageId,
            'media' => $media,
        ], $options);

        $files = $this->extractFiles($parameters);
        $parameters = $this->prepareParameters($parameters);
        
        if (!empty($files)) {
            $response = $this->upload('editMessageMedia', $parameters, $files);
        } else {
            $response = $this->call('editMessageMedia', $parameters);
        }

        return $response->toDTO(Message::class);
    }

    /**
     * 编辑消息回复标记
     */
    public function editMessageReplyMarkup(
        int|string $chatId,
        int $messageId,
        array $replyMarkup = null
    ): Message {
        $this->validateChatId($chatId);
        $this->validateMessageId($messageId);

        $parameters = $this->prepareParameters([
            'chat_id' => $chatId,
            'message_id' => $messageId,
            'reply_markup' => $replyMarkup,
        ]);

        $response = $this->call('editMessageReplyMarkup', $parameters);
        return $response->toDTO(Message::class);
    }

    /**
     * 停止投票
     */
    public function stopPoll(
        int|string $chatId,
        int $messageId,
        array $replyMarkup = null
    ): array {
        $this->validateChatId($chatId);
        $this->validateMessageId($messageId);

        $parameters = $this->prepareParameters([
            'chat_id' => $chatId,
            'message_id' => $messageId,
            'reply_markup' => $replyMarkup,
        ]);

        $response = $this->call('stopPoll', $parameters);
        return $response->getResult();
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

        $parameters = $this->prepareParameters([
            'chat_id' => $chatId,
            'message_id' => $messageId,
        ]);

        $response = $this->call('deleteMessage', $parameters);
        return (bool) $response->getResult();
    }

    /**
     * 批量删除消息
     */
    public function deleteMessages(
        int|string $chatId,
        array $messageIds
    ): bool {
        $this->validateChatId($chatId);

        if (empty($messageIds)) {
            throw new \InvalidArgumentException('Message IDs array cannot be empty');
        }

        foreach ($messageIds as $messageId) {
            $this->validateMessageId($messageId);
        }

        $parameters = $this->prepareParameters([
            'chat_id' => $chatId,
            'message_ids' => $messageIds,
        ]);

        $response = $this->call('deleteMessages', $parameters);
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

        $parameters = $this->prepareParameters(array_merge([
            'chat_id' => $chatId,
            'from_chat_id' => $fromChatId,
            'message_id' => $messageId,
        ], $options));

        $response = $this->call('forwardMessage', $parameters);
        return $response->toDTO(Message::class);
    }

    /**
     * 批量转发消息
     */
    public function forwardMessages(
        int|string $chatId,
        int|string $fromChatId,
        array $messageIds,
        array $options = []
    ): array {
        $this->validateChatId($chatId);
        $this->validateChatId($fromChatId);

        if (empty($messageIds)) {
            throw new \InvalidArgumentException('Message IDs array cannot be empty');
        }

        foreach ($messageIds as $messageId) {
            $this->validateMessageId($messageId);
        }

        $parameters = $this->prepareParameters(array_merge([
            'chat_id' => $chatId,
            'from_chat_id' => $fromChatId,
            'message_ids' => $messageIds,
        ], $options));

        $response = $this->call('forwardMessages', $parameters);
        return $response->getResult();
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

        $parameters = $this->prepareParameters(array_merge([
            'chat_id' => $chatId,
            'from_chat_id' => $fromChatId,
            'message_id' => $messageId,
        ], $options));

        $response = $this->call('copyMessage', $parameters);
        return (int) $response->getResult()['message_id'];
    }

    /**
     * 批量复制消息
     */
    public function copyMessages(
        int|string $chatId,
        int|string $fromChatId,
        array $messageIds,
        array $options = []
    ): array {
        $this->validateChatId($chatId);
        $this->validateChatId($fromChatId);

        if (empty($messageIds)) {
            throw new \InvalidArgumentException('Message IDs array cannot be empty');
        }

        foreach ($messageIds as $messageId) {
            $this->validateMessageId($messageId);
        }

        $parameters = $this->prepareParameters(array_merge([
            'chat_id' => $chatId,
            'from_chat_id' => $fromChatId,
            'message_ids' => $messageIds,
        ], $options));

        $response = $this->call('copyMessages', $parameters);
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
        $this->validateChatId($chatId);

        $parameters = array_merge([
            'chat_id' => $chatId,
            'photo' => $photo,
        ], $options);

        $files = $this->extractFiles($parameters);
        $parameters = $this->prepareParameters($parameters);
        
        if (!empty($files)) {
            $response = $this->upload('sendPhoto', $parameters, $files);
        } else {
            $response = $this->call('sendPhoto', $parameters);
        }

        return $response->toDTO(Message::class);
    }

    /**
     * 发送音频
     */
    public function sendAudio(
        int|string $chatId,
        string $audio,
        array $options = []
    ): Message {
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

        return $response->toDTO(Message::class);
    }

    /**
     * 发送文档
     */
    public function sendDocument(
        int|string $chatId,
        string $document,
        array $options = []
    ): Message {
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

        return $response->toDTO(Message::class);
    }

    /**
     * 发送视频
     */
    public function sendVideo(
        int|string $chatId,
        string $video,
        array $options = []
    ): Message {
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

        return $response->toDTO(Message::class);
    }

    /**
     * 发送动画
     */
    public function sendAnimation(
        int|string $chatId,
        string $animation,
        array $options = []
    ): Message {
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

        return $response->toDTO(Message::class);
    }

    /**
     * 发送语音
     */
    public function sendVoice(
        int|string $chatId,
        string $voice,
        array $options = []
    ): Message {
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

        return $response->toDTO(Message::class);
    }

    /**
     * 发送视频笔记
     */
    public function sendVideoNote(
        int|string $chatId,
        string $videoNote,
        array $options = []
    ): Message {
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

        return $response->toDTO(Message::class);
    }

    /**
     * 发送媒体组
     */
    public function sendMediaGroup(
        int|string $chatId,
        array $media,
        array $options = []
    ): array {
        $this->validateChatId($chatId);

        if (empty($media)) {
            throw new \InvalidArgumentException('Media array cannot be empty');
        }

        if (count($media) > 10) {
            throw new \InvalidArgumentException('Media array cannot contain more than 10 items');
        }

        $parameters = array_merge([
            'chat_id' => $chatId,
            'media' => $media,
        ], $options);

        $files = $this->extractFiles($parameters);
        $parameters = $this->prepareParameters($parameters);

        if (!empty($files)) {
            $response = $this->upload('sendMediaGroup', $parameters, $files);
        } else {
            $response = $this->call('sendMediaGroup', $parameters);
        }

        return $response->toDTOArray(Message::class);
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
        $this->validateCoordinates($latitude, $longitude);

        $parameters = $this->prepareParameters(array_merge([
            'chat_id' => $chatId,
            'latitude' => $latitude,
            'longitude' => $longitude,
        ], $options));

        $response = $this->call('sendLocation', $parameters);
        return $response->toDTO(Message::class);
    }

    /**
     * 编辑实时位置
     */
    public function editMessageLiveLocation(
        int|string $chatId,
        int $messageId,
        float $latitude,
        float $longitude,
        array $options = []
    ): Message {
        $this->validateChatId($chatId);
        $this->validateMessageId($messageId);
        $this->validateCoordinates($latitude, $longitude);

        $parameters = $this->prepareParameters(array_merge([
            'chat_id' => $chatId,
            'message_id' => $messageId,
            'latitude' => $latitude,
            'longitude' => $longitude,
        ], $options));

        $response = $this->call('editMessageLiveLocation', $parameters);
        return $response->toDTO(Message::class);
    }

    /**
     * 停止实时位置
     */
    public function stopMessageLiveLocation(
        int|string $chatId,
        int $messageId,
        array $replyMarkup = null
    ): Message {
        $this->validateChatId($chatId);
        $this->validateMessageId($messageId);

        $parameters = $this->prepareParameters([
            'chat_id' => $chatId,
            'message_id' => $messageId,
            'reply_markup' => $replyMarkup,
        ]);

        $response = $this->call('stopMessageLiveLocation', $parameters);
        return $response->toDTO(Message::class);
    }

    /**
     * 发送场地
     */
    public function sendVenue(
        int|string $chatId,
        float $latitude,
        float $longitude,
        string $title,
        string $address,
        array $options = []
    ): Message {
        $this->validateChatId($chatId);
        $this->validateCoordinates($latitude, $longitude);

        if (empty($title)) {
            throw new \InvalidArgumentException('Title cannot be empty');
        }

        if (empty($address)) {
            throw new \InvalidArgumentException('Address cannot be empty');
        }

        $parameters = $this->prepareParameters(array_merge([
            'chat_id' => $chatId,
            'latitude' => $latitude,
            'longitude' => $longitude,
            'title' => $title,
            'address' => $address,
        ], $options));

        $response = $this->call('sendVenue', $parameters);
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
            throw new \InvalidArgumentException('Phone number cannot be empty');
        }

        if (empty($firstName)) {
            throw new \InvalidArgumentException('First name cannot be empty');
        }

        $parameters = $this->prepareParameters(array_merge([
            'chat_id' => $chatId,
            'phone_number' => $phoneNumber,
            'first_name' => $firstName,
        ], $options));

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
            throw new \InvalidArgumentException('Question cannot be empty');
        }

        if (empty($options) || count($options) < 2) {
            throw new \InvalidArgumentException('Poll must have at least 2 options');
        }

        if (count($options) > 10) {
            throw new \InvalidArgumentException('Poll cannot have more than 10 options');
        }

        $parameters = $this->prepareParameters(array_merge([
            'chat_id' => $chatId,
            'question' => $question,
            'options' => $options,
        ], $settings));

        $response = $this->call('sendPoll', $parameters);
        return $response->toDTO(Message::class);
    }

    /**
     * 发送骰子
     */
    public function sendDice(
        int|string $chatId,
        array $options = []
    ): Message {
        $this->validateChatId($chatId);

        $parameters = $this->prepareParameters(array_merge([
            'chat_id' => $chatId,
        ], $options));

        $response = $this->call('sendDice', $parameters);
        return $response->toDTO(Message::class);
    }
}