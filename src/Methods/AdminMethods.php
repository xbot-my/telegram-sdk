<?php

declare(strict_types=1);

namespace XBot\Telegram\Methods;

/**
 * 管理员方法组
 * 
 * 提供聊天管理相关的 API 方法
 */
class AdminMethods extends BaseMethodGroup
{
    /**
     * 获取 HTTP 客户端
     */
    public function getHttpClient(): \XBot\Telegram\Contracts\HttpClientInterface
    {
        return $this->httpClient;
    }

    /**
     * 踢出聊天成员
     */
    public function kickChatMember(int|string $chatId, int $userId, ?int $untilDate = null): bool
    {
        $this->validateChatId($chatId);
        $this->validateUserId($userId);

        $parameters = [
            'chat_id' => $chatId,
            'user_id' => $userId,
        ];

        if ($untilDate !== null) {
            $parameters['until_date'] = $untilDate;
        }

        $response = $this->call('kickChatMember', $parameters);
        return $response->isOk() && $response->getResult() === true;
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
        return $response->isOk() && $response->getResult() === true;
    }

    /**
     * 解封聊天成员
     */
    public function unbanChatMember(
        int|string $chatId, 
        int $userId, 
        bool $onlyIfBanned = true
    ): bool {
        $this->validateChatId($chatId);
        $this->validateUserId($userId);

        $parameters = [
            'chat_id' => $chatId,
            'user_id' => $userId,
            'only_if_banned' => $onlyIfBanned,
        ];

        $response = $this->call('unbanChatMember', $parameters);
        return $response->isOk() && $response->getResult() === true;
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
            'permissions' => json_encode($permissions),
        ], $options);

        $response = $this->call('restrictChatMember', $parameters);
        return $response->isOk() && $response->getResult() === true;
    }

    /**
     * 提升聊天成员为管理员
     */
    public function promoteChatMember(
        int|string $chatId, 
        int $userId, 
        array $privileges = []
    ): bool {
        $this->validateChatId($chatId);
        $this->validateUserId($userId);

        $parameters = array_merge([
            'chat_id' => $chatId,
            'user_id' => $userId,
        ], $privileges);

        $response = $this->call('promoteChatMember', $parameters);
        return $response->isOk() && $response->getResult() === true;
    }

    /**
     * 设置聊天管理员自定义标题
     */
    public function setChatAdministratorCustomTitle(
        int|string $chatId, 
        int $userId, 
        string $customTitle
    ): bool {
        $this->validateChatId($chatId);
        $this->validateUserId($userId);

        if (strlen($customTitle) > 16) {
            throw new \InvalidArgumentException('Custom title cannot exceed 16 characters');
        }

        $parameters = [
            'chat_id' => $chatId,
            'user_id' => $userId,
            'custom_title' => $customTitle,
        ];

        $response = $this->call('setChatAdministratorCustomTitle', $parameters);
        return $response->isOk() && $response->getResult() === true;
    }

    /**
     * 封禁聊天发送者
     */
    public function banChatSenderChat(int|string $chatId, int $senderChatId): bool
    {
        $this->validateChatId($chatId);

        if ($senderChatId <= 0) {
            throw new \InvalidArgumentException('Sender chat ID must be a positive integer');
        }

        $parameters = [
            'chat_id' => $chatId,
            'sender_chat_id' => $senderChatId,
        ];

        $response = $this->call('banChatSenderChat', $parameters);
        return $response->isOk() && $response->getResult() === true;
    }

    /**
     * 解封聊天发送者
     */
    public function unbanChatSenderChat(int|string $chatId, int $senderChatId): bool
    {
        $this->validateChatId($chatId);

        if ($senderChatId <= 0) {
            throw new \InvalidArgumentException('Sender chat ID must be a positive integer');
        }

        $parameters = [
            'chat_id' => $chatId,
            'sender_chat_id' => $senderChatId,
        ];

        $response = $this->call('unbanChatSenderChat', $parameters);
        return $response->isOk() && $response->getResult() === true;
    }

    /**
     * 获取聊天管理员
     */
    public function getChatAdministrators(int|string $chatId): mixed
    {
        $this->validateChatId($chatId);

        $parameters = ['chat_id' => $chatId];
        $response = $this->call('getChatAdministrators', $parameters)->ensureOk();
        return $this->formatResult($response->getResult());
    }

    /**
     * 获取聊天成员数量
     */
    public function getChatMemberCount(int|string $chatId): int
    {
        $this->validateChatId($chatId);

        $parameters = ['chat_id' => $chatId];
        $response = $this->call('getChatMemberCount', $parameters)->ensureOk();
        return (int) $response->getResult();
    }

    /**
     * 获取聊天成员信息
     */
    public function getChatMember(int|string $chatId, int $userId): mixed
    {
        $this->validateChatId($chatId);
        $this->validateUserId($userId);

        $parameters = [
            'chat_id' => $chatId,
            'user_id' => $userId,
        ];

        $response = $this->call('getChatMember', $parameters)->ensureOk();
        return $this->formatResult($response->getResult());
    }

    /**
     * 设置聊天权限
     */
    public function setChatPermissions(int|string $chatId, array $permissions): bool
    {
        $this->validateChatId($chatId);

        $parameters = [
            'chat_id' => $chatId,
            'permissions' => json_encode($permissions),
        ];

        $response = $this->call('setChatPermissions', $parameters);
        return $response->isOk() && $response->getResult() === true;
    }

    /**
     * 导出聊天邀请链接
     */
    public function exportChatInviteLink(int|string $chatId): ?string
    {
        $this->validateChatId($chatId);

        $parameters = ['chat_id' => $chatId];
        $response = $this->call('exportChatInviteLink', $parameters)->ensureOk();
        $result = $response->getResult();
        return is_string($result) ? $result : null;
    }

    /**
     * 创建聊天邀请链接
     */
    public function createChatInviteLink(int|string $chatId, array $options = []): mixed
    {
        $this->validateChatId($chatId);

        $parameters = array_merge(['chat_id' => $chatId], $options);
        $response = $this->call('createChatInviteLink', $parameters)->ensureOk();
        return $this->formatResult($response->getResult());
    }

    /**
     * 编辑聊天邀请链接
     */
    public function editChatInviteLink(
        int|string $chatId, 
        string $inviteLink, 
        array $options = []
    ): mixed {
        $this->validateChatId($chatId);
        $this->validateUrl($inviteLink);

        $parameters = array_merge([
            'chat_id' => $chatId,
            'invite_link' => $inviteLink,
        ], $options);

        $response = $this->call('editChatInviteLink', $parameters)->ensureOk();
        return $this->formatResult($response->getResult());
    }

    /**
     * 撤销聊天邀请链接
     */
    public function revokeChatInviteLink(int|string $chatId, string $inviteLink): mixed
    {
        $this->validateChatId($chatId);
        $this->validateUrl($inviteLink);

        $parameters = [
            'chat_id' => $chatId,
            'invite_link' => $inviteLink,
        ];

        $response = $this->call('revokeChatInviteLink', $parameters)->ensureOk();
        return $this->formatResult($response->getResult());
    }

    /**
     * 批准加入聊天请求
     */
    public function approveChatJoinRequest(int|string $chatId, int $userId): bool
    {
        $this->validateChatId($chatId);
        $this->validateUserId($userId);

        $parameters = [
            'chat_id' => $chatId,
            'user_id' => $userId,
        ];

        $response = $this->call('approveChatJoinRequest', $parameters);
        return $response->isOk() && $response->getResult() === true;
    }

    /**
     * 拒绝加入聊天请求
     */
    public function declineChatJoinRequest(int|string $chatId, int $userId): bool
    {
        $this->validateChatId($chatId);
        $this->validateUserId($userId);

        $parameters = [
            'chat_id' => $chatId,
            'user_id' => $userId,
        ];

        $response = $this->call('declineChatJoinRequest', $parameters);
        return $response->isOk() && $response->getResult() === true;
    }
}
