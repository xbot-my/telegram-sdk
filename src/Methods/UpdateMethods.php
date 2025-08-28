<?php

declare(strict_types=1);

namespace XBot\Telegram\Methods;

use XBot\Telegram\Models\DTO\Update;
use XBot\Telegram\Models\DTO\User;
use XBot\Telegram\Models\Response\PaginatedResponse;

/**
 * 更新相关的 API 方法
 * 
 * 包含获取更新、设置 Webhook 等相关功能
 */
class UpdateMethods extends BaseMethodGroup
{
    /**
     * 获取 Bot 信息
     */
    public function getMe(): User
    {
        $response = $this->call('getMe');
        return $response->toDTO(User::class);
    }

    /**
     * 注销 Bot
     */
    public function logOut(): bool
    {
        $response = $this->call('logOut');
        return (bool) $response->getResult();
    }

    /**
     * 关闭 Bot
     */
    public function close(): bool
    {
        $response = $this->call('close');
        return (bool) $response->getResult();
    }

    /**
     * 获取更新
     */
    public function getUpdates(array $options = []): array
    {
        $parameters = $this->prepareParameters($options);
        $response = $this->call('getUpdates', $parameters);
        return $response->toDTOArray(Update::class);
    }

    /**
     * 获取分页更新
     */
    public function getUpdatesPaginated(
        int $offset = 0,
        int $limit = 100,
        int $timeout = 0,
        array $allowedUpdates = []
    ): PaginatedResponse {
        $parameters = $this->prepareParameters([
            'offset' => $offset,
            'limit' => $limit,
            'timeout' => $timeout,
            'allowed_updates' => $allowedUpdates,
        ]);

        $response = $this->call('getUpdates', $parameters);
        
        return new PaginatedResponse(
            $response->getRawResponse(),
            page: intval($offset / $limit) + 1,
            perPage: $limit,
            offset: $offset,
            statusCode: $response->getStatusCode(),
            headers: $response->getHeaders(),
            botName: $this->botName
        );
    }

    /**
     * 设置 Webhook
     */
    public function setWebhook(string $url, array $options = []): bool
    {
        $this->validateUrl($url, true); // Webhook 必须使用 HTTPS

        $parameters = $this->prepareParameters(array_merge([
            'url' => $url,
        ], $options));

        // 检查是否有证书文件需要上传
        $files = [];
        if (isset($options['certificate']) && $this->isFilePath($options['certificate'])) {
            $files['certificate'] = $options['certificate'];
            unset($parameters['certificate']);
        }

        if (!empty($files)) {
            $response = $this->upload('setWebhook', $parameters, $files);
        } else {
            $response = $this->call('setWebhook', $parameters);
        }

        return (bool) $response->getResult();
    }

    /**
     * 删除 Webhook
     */
    public function deleteWebhook(bool $dropPendingUpdates = false): bool
    {
        $parameters = $this->prepareParameters([
            'drop_pending_updates' => $dropPendingUpdates,
        ]);

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
     * 设置我的命令
     */
    public function setMyCommands(array $commands, array $options = []): bool
    {
        if (empty($commands)) {
            throw new \InvalidArgumentException('Commands array cannot be empty');
        }

        // 验证命令格式
        foreach ($commands as $command) {
            if (!isset($command['command']) || !isset($command['description'])) {
                throw new \InvalidArgumentException('Each command must have "command" and "description" fields');
            }

            if (!preg_match('/^[a-z0-9_]{1,32}$/', $command['command'])) {
                throw new \InvalidArgumentException('Command name must be 1-32 characters long and contain only lowercase letters, numbers and underscores');
            }

            if (strlen($command['description']) > 256) {
                throw new \InvalidArgumentException('Command description cannot exceed 256 characters');
            }
        }

        $parameters = $this->prepareParameters(array_merge([
            'commands' => $commands,
        ], $options));

        $response = $this->call('setMyCommands', $parameters);
        return (bool) $response->getResult();
    }

    /**
     * 删除我的命令
     */
    public function deleteMyCommands(array $options = []): bool
    {
        $parameters = $this->prepareParameters($options);
        $response = $this->call('deleteMyCommands', $parameters);
        return (bool) $response->getResult();
    }

    /**
     * 获取我的命令
     */
    public function getMyCommands(array $options = []): array
    {
        $parameters = $this->prepareParameters($options);
        $response = $this->call('getMyCommands', $parameters);
        return $response->getResult();
    }

    /**
     * 设置我的名称
     */
    public function setMyName(string $name = '', string $languageCode = ''): bool
    {
        if (strlen($name) > 64) {
            throw new \InvalidArgumentException('Bot name cannot exceed 64 characters');
        }

        $parameters = $this->prepareParameters([
            'name' => $name,
            'language_code' => $languageCode,
        ]);

        $response = $this->call('setMyName', $parameters);
        return (bool) $response->getResult();
    }

    /**
     * 获取我的名称
     */
    public function getMyName(string $languageCode = ''): array
    {
        $parameters = $this->prepareParameters([
            'language_code' => $languageCode,
        ]);

        $response = $this->call('getMyName', $parameters);
        return $response->getResult();
    }

    /**
     * 设置我的描述
     */
    public function setMyDescription(string $description = '', string $languageCode = ''): bool
    {
        if (strlen($description) > 512) {
            throw new \InvalidArgumentException('Bot description cannot exceed 512 characters');
        }

        $parameters = $this->prepareParameters([
            'description' => $description,
            'language_code' => $languageCode,
        ]);

        $response = $this->call('setMyDescription', $parameters);
        return (bool) $response->getResult();
    }

    /**
     * 获取我的描述
     */
    public function getMyDescription(string $languageCode = ''): array
    {
        $parameters = $this->prepareParameters([
            'language_code' => $languageCode,
        ]);

        $response = $this->call('getMyDescription', $parameters);
        return $response->getResult();
    }

    /**
     * 设置我的简短描述
     */
    public function setMyShortDescription(string $shortDescription = '', string $languageCode = ''): bool
    {
        if (strlen($shortDescription) > 120) {
            throw new \InvalidArgumentException('Bot short description cannot exceed 120 characters');
        }

        $parameters = $this->prepareParameters([
            'short_description' => $shortDescription,
            'language_code' => $languageCode,
        ]);

        $response = $this->call('setMyShortDescription', $parameters);
        return (bool) $response->getResult();
    }

    /**
     * 获取我的简短描述
     */
    public function getMyShortDescription(string $languageCode = ''): array
    {
        $parameters = $this->prepareParameters([
            'language_code' => $languageCode,
        ]);

        $response = $this->call('getMyShortDescription', $parameters);
        return $response->getResult();
    }

    /**
     * 设置聊天菜单按钮
     */
    public function setChatMenuButton(int|string $chatId = null, array $menuButton = null): bool
    {
        $parameters = $this->prepareParameters([
            'chat_id' => $chatId,
            'menu_button' => $menuButton,
        ]);

        $response = $this->call('setChatMenuButton', $parameters);
        return (bool) $response->getResult();
    }

    /**
     * 获取聊天菜单按钮
     */
    public function getChatMenuButton(int|string $chatId = null): array
    {
        $parameters = $this->prepareParameters([
            'chat_id' => $chatId,
        ]);

        $response = $this->call('getChatMenuButton', $parameters);
        return $response->getResult();
    }

    /**
     * 设置我的默认管理员权限
     */
    public function setMyDefaultAdministratorRights(array $rights = null, bool $forChannels = false): bool
    {
        $parameters = $this->prepareParameters([
            'rights' => $rights,
            'for_channels' => $forChannels,
        ]);

        $response = $this->call('setMyDefaultAdministratorRights', $parameters);
        return (bool) $response->getResult();
    }

    /**
     * 获取我的默认管理员权限
     */
    public function getMyDefaultAdministratorRights(bool $forChannels = false): array
    {
        $parameters = $this->prepareParameters([
            'for_channels' => $forChannels,
        ]);

        $response = $this->call('getMyDefaultAdministratorRights', $parameters);
        return $response->getResult();
    }

    /**
     * 回答回调查询
     */
    public function answerCallbackQuery(string $callbackQueryId, array $options = []): bool
    {
        if (empty($callbackQueryId)) {
            throw new \InvalidArgumentException('Callback query ID cannot be empty');
        }

        $parameters = $this->prepareParameters(array_merge([
            'callback_query_id' => $callbackQueryId,
        ], $options));

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
            throw new \InvalidArgumentException('Inline query ID cannot be empty');
        }

        if (count($results) > 50) {
            throw new \InvalidArgumentException('Results array cannot contain more than 50 items');
        }

        $parameters = $this->prepareParameters(array_merge([
            'inline_query_id' => $inlineQueryId,
            'results' => $results,
        ], $options));

        $response = $this->call('answerInlineQuery', $parameters);
        return (bool) $response->getResult();
    }

    /**
     * 回答 Web App 查询
     */
    public function answerWebAppQuery(string $webAppQueryId, array $result): array
    {
        if (empty($webAppQueryId)) {
            throw new \InvalidArgumentException('Web app query ID cannot be empty');
        }

        $parameters = $this->prepareParameters([
            'web_app_query_id' => $webAppQueryId,
            'result' => $result,
        ]);

        $response = $this->call('answerWebAppQuery', $parameters);
        return $response->getResult();
    }

    /**
     * 获取文件
     */
    public function getFile(string $fileId): array
    {
        if (empty($fileId)) {
            throw new \InvalidArgumentException('File ID cannot be empty');
        }

        $parameters = $this->prepareParameters([
            'file_id' => $fileId,
        ]);

        $response = $this->call('getFile', $parameters);
        return $response->getResult();
    }

    /**
     * 获取文件下载链接
     */
    public function getFileDownloadUrl(string $fileId): string
    {
        $file = $this->getFile($fileId);
        
        if (!isset($file['file_path'])) {
            throw new \RuntimeException('File path not available');
        }

        $baseUrl = str_replace('/bot', '/file/bot', $this->httpClient->getBaseUrl());
        return $baseUrl . $this->httpClient->getToken() . '/' . $file['file_path'];
    }

    /**
     * 下载文件内容
     */
    public function downloadFile(string $fileId): string
    {
        $url = $this->getFileDownloadUrl($fileId);
        
        // 使用简单的文件下载
        $content = file_get_contents($url);
        
        if ($content === false) {
            throw new \RuntimeException('Failed to download file');
        }

        return $content;
    }

    /**
     * 获取用户个人资料照片
     */
    public function getUserProfilePhotos(int $userId, array $options = []): array
    {
        $this->validateUserId($userId);

        $parameters = $this->prepareParameters(array_merge([
            'user_id' => $userId,
        ], $options));

        $response = $this->call('getUserProfilePhotos', $parameters);
        return $response->getResult();
    }
}