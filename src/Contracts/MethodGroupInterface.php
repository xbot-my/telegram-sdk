<?php

declare(strict_types=1);

namespace XBot\Telegram\Contracts;

use XBot\Telegram\Models\Response\TelegramResponse;

/**
 * 方法组接口
 * 
 * 定义所有 API 方法组的标准接口
 */
interface MethodGroupInterface
{
    /**
     * 获取 Bot 名称
     */
    public function getBotName(): string;

    /**
     * 获取 HTTP 客户端
     */
    public function getHttpClient(): HttpClientInterface;
}