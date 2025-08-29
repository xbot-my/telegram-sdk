<?php

declare(strict_types=1);

namespace XBot\Telegram\Methods;
use XBot\Telegram\Models\Response\TelegramResponse;

/**
 * 内联查询方法组
 * 
 * 提供内联查询和内联键盘相关的 API 方法
 */
class InlineMethods extends BaseMethodGroup
{
    /**
     * 获取 HTTP 客户端
     */
    public function getHttpClient(): \XBot\Telegram\Contracts\HttpClientInterface
    {
        return $this->httpClient;
    }

    /**
     * 回应内联查询
     */
    public function answerInlineQuery(
        string $inlineQueryId, 
        array $results, 
        array $options = []
    ): bool {
        if (empty($inlineQueryId)) {
            throw new \InvalidArgumentException('Inline query ID cannot be empty');
        }

        if (count($results) > 50) {
            throw new \InvalidArgumentException('Results array cannot contain more than 50 items');
        }

        $parameters = array_merge([
            'inline_query_id' => $inlineQueryId,
            'results' => json_encode($results),
        ], $options);

        $response = $this->call('answerInlineQuery', $parameters);
        return $response->isOk() && $response->getResult() === true;
    }

    /**
     * 回应回调查询
     */
    public function answerCallbackQuery(
        string $callbackQueryId, 
        array $options = []
    ): bool {
        if (empty($callbackQueryId)) {
            throw new \InvalidArgumentException('Callback query ID cannot be empty');
        }

        $parameters = array_merge([
            'callback_query_id' => $callbackQueryId,
        ], $options);

        // 验证文本长度（如果提供了）
        if (isset($options['text'])) {
            $this->validateTextLength($options['text'], 200);
        }

        $response = $this->call('answerCallbackQuery', $parameters);
        return $response->isOk() && $response->getResult() === true;
    }

    /**
     * 编辑消息文本（内联键盘）
     */
    public function editMessageText(
        string $text, 
        array $options = []
    ): bool|array {
        $this->validateTextLength($text, 4096);

        $parameters = array_merge([
            'text' => $text,
        ], $options);

        $response = $this->call('editMessageText', $parameters);

        if (!$response->isOk()) {
            return false;
        }

        $result = $response->getResult();
        
        // 如果返回 true，表示消息没有变化
        if ($result === true) {
            return true;
        }

        // 如果返回消息对象，返回数组格式
        return is_array($result) ? $result : false;
    }

    /**
     * 编辑消息标题
     */
    public function editMessageCaption(array $options = []): bool|array
    {
        // 验证标题长度（如果提供了）
        if (isset($options['caption'])) {
            $this->validateTextLength($options['caption'], 1024);
        }

        $response = $this->call('editMessageCaption', $options);

        if (!$response->isOk()) {
            return false;
        }

        $result = $response->getResult();
        
        if ($result === true) {
            return true;
        }

        return is_array($result) ? $result : false;
    }

    /**
     * 编辑消息媒体
     */
    public function editMessageMedia(
        array $media, 
        array $options = []
    ): bool|array {
        if (empty($media)) {
            throw new \InvalidArgumentException('Media object cannot be empty');
        }

        $parameters = array_merge([
            'media' => json_encode($media),
        ], $options);

        // 处理媒体文件上传
        $files = [];
        if (isset($media['media']) && is_string($media['media']) && $this->isFilePath($media['media'])) {
            $files['media'] = $media['media'];
            $media['media'] = 'attach://media';
            $parameters['media'] = json_encode($media);
        }

        if (!empty($files)) {
            $response = $this->upload('editMessageMedia', $parameters, $files);
        } else {
            $response = $this->call('editMessageMedia', $parameters);
        }

        if (!$response->isOk()) {
            return false;
        }

        $result = $response->getResult();
        
        if ($result === true) {
            return true;
        }

        return is_array($result) ? $result : false;
    }

    /**
     * 编辑消息回复标记
     */
    public function editMessageReplyMarkup(array $options = []): bool|array
    {
        $response = $this->call('editMessageReplyMarkup', $options);

        if (!$response->isOk()) {
            return false;
        }

        $result = $response->getResult();
        
        if ($result === true) {
            return true;
        }

        return is_array($result) ? $result : false;
    }

    /**
     * 停止投票
     */
    public function stopPoll(
        int|string $chatId, 
        int $messageId, 
        array $options = []
    ): ?array {
        $this->validateChatId($chatId);
        $this->validateMessageId($messageId);

        $parameters = array_merge([
            'chat_id' => $chatId,
            'message_id' => $messageId,
        ], $options);

        $response = $this->call('stopPoll', $parameters);

        if (!$response->isOk()) {
            return null;
        }

        $result = $response->getResult();
        return is_array($result) ? $result : null;
    }

    /**
     * 删除消息
     */
    public function deleteMessage(int|string $chatId, int $messageId): bool
    {
        $this->validateChatId($chatId);
        $this->validateMessageId($messageId);

        $parameters = [
            'chat_id' => $chatId,
            'message_id' => $messageId,
        ];

        $response = $this->call('deleteMessage', $parameters);
        return $response->isOk() && $response->getResult() === true;
    }

    /**
     * 创建内联查询结果 - 文章
     */
    public function createInlineQueryResultArticle(
        string $id,
        string $title,
        string $inputMessageContent,
        array $options = []
    ): array {
        if (empty($id)) {
            throw new \InvalidArgumentException('Result ID cannot be empty');
        }

        if (empty($title)) {
            throw new \InvalidArgumentException('Title cannot be empty');
        }

        $result = array_merge([
            'type' => 'article',
            'id' => $id,
            'title' => $title,
            'input_message_content' => [
                'message_text' => $inputMessageContent,
            ],
        ], $options);

        return $result;
    }

    /**
     * 创建内联查询结果 - 照片
     */
    public function createInlineQueryResultPhoto(
        string $id,
        string $photoUrl,
        string $thumbnailUrl,
        array $options = []
    ): array {
        if (empty($id)) {
            throw new \InvalidArgumentException('Result ID cannot be empty');
        }

        $this->validateUrl($photoUrl, true);
        $this->validateUrl($thumbnailUrl, true);

        $result = array_merge([
            'type' => 'photo',
            'id' => $id,
            'photo_url' => $photoUrl,
            'thumbnail_url' => $thumbnailUrl,
        ], $options);

        return $result;
    }

    /**
     * 创建内联查询结果 - GIF
     */
    public function createInlineQueryResultGif(
        string $id,
        string $gifUrl,
        string $thumbnailUrl,
        array $options = []
    ): array {
        if (empty($id)) {
            throw new \InvalidArgumentException('Result ID cannot be empty');
        }

        $this->validateUrl($gifUrl, true);
        $this->validateUrl($thumbnailUrl, true);

        $result = array_merge([
            'type' => 'gif',
            'id' => $id,
            'gif_url' => $gifUrl,
            'thumbnail_url' => $thumbnailUrl,
        ], $options);

        return $result;
    }

    /**
     * 创建内联查询结果 - 视频
     */
    public function createInlineQueryResultVideo(
        string $id,
        string $videoUrl,
        string $mimeType,
        string $title,
        string $thumbnailUrl,
        array $options = []
    ): array {
        if (empty($id)) {
            throw new \InvalidArgumentException('Result ID cannot be empty');
        }

        if (empty($title)) {
            throw new \InvalidArgumentException('Title cannot be empty');
        }

        $this->validateUrl($videoUrl, true);
        $this->validateUrl($thumbnailUrl, true);

        $result = array_merge([
            'type' => 'video',
            'id' => $id,
            'video_url' => $videoUrl,
            'mime_type' => $mimeType,
            'title' => $title,
            'thumbnail_url' => $thumbnailUrl,
        ], $options);

        return $result;
    }

    /**
     * 创建内联键盘标记
     */
    public function createInlineKeyboardMarkup(array $keyboard): array
    {
        if (empty($keyboard)) {
            throw new \InvalidArgumentException('Keyboard cannot be empty');
        }

        return [
            'inline_keyboard' => $keyboard,
        ];
    }

    /**
     * 创建内联键盘按钮
     */
    public function createInlineKeyboardButton(
        string $text, 
        array $options = []
    ): array {
        if (empty($text)) {
            throw new \InvalidArgumentException('Button text cannot be empty');
        }

        $button = array_merge(['text' => $text], $options);

        // 验证按钮必须有一个操作
        $actions = ['url', 'callback_data', 'web_app', 'login_url', 'switch_inline_query', 'switch_inline_query_current_chat'];
        $hasAction = false;

        foreach ($actions as $action) {
            if (isset($button[$action])) {
                $hasAction = true;
                break;
            }
        }

        if (!$hasAction) {
            throw new \InvalidArgumentException('Button must have at least one action (url, callback_data, etc.)');
        }

        return $button;
    }

    /**
     * 设置我的命令
     */
    public function setMyCommands(array $commands, array $options = []): bool
    {
        if (count($commands) > 100) {
            throw new \InvalidArgumentException('Commands array cannot contain more than 100 items');
        }

        $parameters = array_merge([
            'commands' => json_encode($commands),
        ], $options);

        $response = $this->call('setMyCommands', $parameters);
        return $response->isOk() && $response->getResult() === true;
    }

    /**
     * 删除我的命令
     */
    public function deleteMyCommands(array $options = []): bool
    {
        $response = $this->call('deleteMyCommands', $options);
        return $response->isOk() && $response->getResult() === true;
    }

    /**
     * 获取我的命令
     */
    public function getMyCommands(array $options = []): mixed
    {
        $response = $this->call('getMyCommands', $options)->ensureOk();
        return $this->formatResult($response->getResult());
    }
}
