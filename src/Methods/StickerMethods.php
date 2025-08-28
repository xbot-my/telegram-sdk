<?php

declare(strict_types=1);

namespace XBot\Telegram\Methods;

use XBot\Telegram\Contracts\MethodGroupInterface;
use XBot\Telegram\Models\Response\TelegramResponse;

/**
 * 贴纸方法组
 * 
 * 提供贴纸相关的 API 方法
 */
class StickerMethods extends BaseMethodGroup implements MethodGroupInterface
{
    /**
     * 获取 HTTP 客户端
     */
    public function getHttpClient(): \XBot\Telegram\Contracts\HttpClientInterface
    {
        return $this->httpClient;
    }

    /**
     * 发送贴纸
     */
    public function sendSticker(
        int|string $chatId, 
        string $sticker, 
        array $options = []
    ): ?array {
        $this->validateChatId($chatId);

        $parameters = array_merge([
            'chat_id' => $chatId,
            'sticker' => $sticker,
        ], $options);

        $files = $this->extractFiles($parameters);
        $parameters = $this->prepareParameters($parameters);

        if (!empty($files)) {
            $response = $this->upload('sendSticker', $parameters, $files);
        } else {
            $response = $this->call('sendSticker', $parameters);
        }

        if (!$response->isOk()) {
            return null;
        }

        $result = $response->getResult();
        return is_array($result) ? $result : null;
    }

    /**
     * 获取贴纸集合
     */
    public function getStickerSet(string $name): ?array
    {
        if (empty($name)) {
            throw new \InvalidArgumentException('Sticker set name cannot be empty');
        }

        $parameters = ['name' => $name];
        $response = $this->call('getStickerSet', $parameters);

        if (!$response->isOk()) {
            return null;
        }

        $result = $response->getResult();
        return is_array($result) ? $result : null;
    }

    /**
     * 获取自定义表情贴纸
     */
    public function getCustomEmojiStickers(array $customEmojiIds): ?array
    {
        if (empty($customEmojiIds)) {
            throw new \InvalidArgumentException('Custom emoji IDs cannot be empty');
        }

        if (count($customEmojiIds) > 200) {
            throw new \InvalidArgumentException('Cannot request more than 200 custom emoji stickers');
        }

        $parameters = [
            'custom_emoji_ids' => json_encode($customEmojiIds),
        ];

        $response = $this->call('getCustomEmojiStickers', $parameters);

        if (!$response->isOk()) {
            return null;
        }

        $result = $response->getResult();
        return is_array($result) ? $result : null;
    }

    /**
     * 上传贴纸文件
     */
    public function uploadStickerFile(
        int $userId, 
        string $sticker, 
        string $stickerFormat
    ): ?array {
        $this->validateUserId($userId);

        if (!in_array($stickerFormat, ['static', 'animated', 'video'])) {
            throw new \InvalidArgumentException('Invalid sticker format. Must be static, animated, or video');
        }

        $parameters = [
            'user_id' => $userId,
            'sticker' => $sticker,
            'sticker_format' => $stickerFormat,
        ];

        $files = $this->extractFiles($parameters);
        $parameters = $this->prepareParameters($parameters);

        if (!empty($files)) {
            $response = $this->upload('uploadStickerFile', $parameters, $files);
        } else {
            $response = $this->call('uploadStickerFile', $parameters);
        }

        if (!$response->isOk()) {
            return null;
        }

        $result = $response->getResult();
        return is_array($result) ? $result : null;
    }

    /**
     * 创建新的贴纸集合
     */
    public function createNewStickerSet(
        int $userId,
        string $name,
        string $title,
        array $stickers,
        string $stickerFormat,
        array $options = []
    ): bool {
        $this->validateUserId($userId);

        if (empty($name)) {
            throw new \InvalidArgumentException('Sticker set name cannot be empty');
        }

        if (empty($title)) {
            throw new \InvalidArgumentException('Sticker set title cannot be empty');
        }

        if (empty($stickers)) {
            throw new \InvalidArgumentException('Stickers array cannot be empty');
        }

        if (!in_array($stickerFormat, ['static', 'animated', 'video'])) {
            throw new \InvalidArgumentException('Invalid sticker format');
        }

        $parameters = array_merge([
            'user_id' => $userId,
            'name' => $name,
            'title' => $title,
            'stickers' => json_encode($stickers),
            'sticker_format' => $stickerFormat,
        ], $options);

        $response = $this->call('createNewStickerSet', $parameters);
        return $response->isOk() && $response->getResult() === true;
    }

    /**
     * 向贴纸集合添加贴纸
     */
    public function addStickerToSet(
        int $userId,
        string $name,
        array $sticker
    ): bool {
        $this->validateUserId($userId);

        if (empty($name)) {
            throw new \InvalidArgumentException('Sticker set name cannot be empty');
        }

        if (empty($sticker)) {
            throw new \InvalidArgumentException('Sticker data cannot be empty');
        }

        $parameters = [
            'user_id' => $userId,
            'name' => $name,
            'sticker' => json_encode($sticker),
        ];

        $response = $this->call('addStickerToSet', $parameters);
        return $response->isOk() && $response->getResult() === true;
    }

    /**
     * 设置贴纸在集合中的位置
     */
    public function setStickerPositionInSet(string $sticker, int $position): bool
    {
        if (empty($sticker)) {
            throw new \InvalidArgumentException('Sticker file ID cannot be empty');
        }

        if ($position < 0) {
            throw new \InvalidArgumentException('Position must be non-negative');
        }

        $parameters = [
            'sticker' => $sticker,
            'position' => $position,
        ];

        $response = $this->call('setStickerPositionInSet', $parameters);
        return $response->isOk() && $response->getResult() === true;
    }

    /**
     * 从贴纸集合中删除贴纸
     */
    public function deleteStickerFromSet(string $sticker): bool
    {
        if (empty($sticker)) {
            throw new \InvalidArgumentException('Sticker file ID cannot be empty');
        }

        $parameters = ['sticker' => $sticker];
        $response = $this->call('deleteStickerFromSet', $parameters);
        return $response->isOk() && $response->getResult() === true;
    }

    /**
     * 设置贴纸集合的缩略图
     */
    public function setStickerSetThumbnail(
        string $name,
        int $userId,
        ?string $thumbnail = null
    ): bool {
        if (empty($name)) {
            throw new \InvalidArgumentException('Sticker set name cannot be empty');
        }

        $this->validateUserId($userId);

        $parameters = [
            'name' => $name,
            'user_id' => $userId,
        ];

        if ($thumbnail !== null) {
            $parameters['thumbnail'] = $thumbnail;
        }

        $files = $this->extractFiles($parameters);
        $parameters = $this->prepareParameters($parameters);

        if (!empty($files)) {
            $response = $this->upload('setStickerSetThumbnail', $parameters, $files);
        } else {
            $response = $this->call('setStickerSetThumbnail', $parameters);
        }

        return $response->isOk() && $response->getResult() === true;
    }
}