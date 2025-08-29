<?php

declare(strict_types=1);

namespace XBot\Telegram\Methods;

use XBot\Telegram\Models\DTO\Chat;

/**
 * 聊天管理相关的 API 方法
 * 
 * 包含聊天信息获取、成员管理、权限设置等功能
 */
class ChatMethods extends BaseMethodGroup
{
    /**
     * 获取聊天信息
     */
    public function getChat(int|string $chatId): mixed
    {
        $this->validateChatId($chatId);

        $parameters = $this->prepareParameters([
            'chat_id' => $chatId,
        ]);

        $response = $this->call('getChat', $parameters)->ensureOk();
        return $this->formatResult($response->getResult());
    }

    /**
     * 获取聊天管理员
     */
    public function getChatAdministrators(int|string $chatId): array
    {
        $this->validateChatId($chatId);

        $parameters = $this->prepareParameters([
            'chat_id' => $chatId,
        ]);

        $response = $this->call('getChatAdministrators', $parameters);
        return $response->getResult();
    }

    /**
     * 获取聊天成员数量
     */
    public function getChatMemberCount(int|string $chatId): int
    {
        $this->validateChatId($chatId);

        $parameters = $this->prepareParameters([
            'chat_id' => $chatId,
        ]);

        $response = $this->call('getChatMemberCount', $parameters);
        return (int) $response->getResult();
    }

    /**
     * 获取聊天成员
     */
    public function getChatMember(int|string $chatId, int $userId): array
    {
        $this->validateChatId($chatId);
        $this->validateUserId($userId);

        $parameters = $this->prepareParameters([
            'chat_id' => $chatId,
            'user_id' => $userId,
        ]);

        $response = $this->call('getChatMember', $parameters);
        return $response->getResult();
    }

    /**
     * 设置聊天贴纸集
     */
    public function setChatStickerSet(int|string $chatId, string $stickerSetName): bool
    {
        $this->validateChatId($chatId);

        if (empty($stickerSetName)) {
            throw new \InvalidArgumentException('Sticker set name cannot be empty');
        }

        $parameters = $this->prepareParameters([
            'chat_id' => $chatId,
            'sticker_set_name' => $stickerSetName,
        ]);

        $response = $this->call('setChatStickerSet', $parameters);
        return (bool) $response->getResult();
    }

    /**
     * 删除聊天贴纸集
     */
    public function deleteChatStickerSet(int|string $chatId): bool
    {
        $this->validateChatId($chatId);

        $parameters = $this->prepareParameters([
            'chat_id' => $chatId,
        ]);

        $response = $this->call('deleteChatStickerSet', $parameters);
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

        $parameters = $this->prepareParameters(array_merge([
            'chat_id' => $chatId,
            'user_id' => $userId,
        ], $options));

        $response = $this->call('banChatMember', $parameters);
        return (bool) $response->getResult();
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

        $parameters = $this->prepareParameters([
            'chat_id' => $chatId,
            'user_id' => $userId,
            'only_if_banned' => $onlyIfBanned,
        ]);

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

        if (empty($permissions)) {
            throw new \InvalidArgumentException('Permissions cannot be empty');
        }

        $parameters = $this->prepareParameters(array_merge([
            'chat_id' => $chatId,
            'user_id' => $userId,
            'permissions' => $permissions,
        ], $options));

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

        $parameters = $this->prepareParameters(array_merge([
            'chat_id' => $chatId,
            'user_id' => $userId,
        ], $options));

        $response = $this->call('promoteChatMember', $parameters);
        return (bool) $response->getResult();
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

        $parameters = $this->prepareParameters([
            'chat_id' => $chatId,
            'user_id' => $userId,
            'custom_title' => $customTitle,
        ]);

        $response = $this->call('setChatAdministratorCustomTitle', $parameters);
        return (bool) $response->getResult();
    }

    /**
     * 设置聊天权限
     */
    public function setChatPermissions(
        int|string $chatId,
        array $permissions,
        bool $useIndependentChatPermissions = false
    ): bool {
        $this->validateChatId($chatId);

        if (empty($permissions)) {
            throw new \InvalidArgumentException('Permissions cannot be empty');
        }

        $parameters = $this->prepareParameters([
            'chat_id' => $chatId,
            'permissions' => $permissions,
            'use_independent_chat_permissions' => $useIndependentChatPermissions,
        ]);

        $response = $this->call('setChatPermissions', $parameters);
        return (bool) $response->getResult();
    }

    /**
     * 导出聊天邀请链接
     */
    public function exportChatInviteLink(int|string $chatId): string
    {
        $this->validateChatId($chatId);

        $parameters = $this->prepareParameters([
            'chat_id' => $chatId,
        ]);

        $response = $this->call('exportChatInviteLink', $parameters);
        return (string) $response->getResult();
    }

    /**
     * 创建聊天邀请链接
     */
    public function createChatInviteLink(
        int|string $chatId,
        array $options = []
    ): array {
        $this->validateChatId($chatId);

        $parameters = $this->prepareParameters(array_merge([
            'chat_id' => $chatId,
        ], $options));

        $response = $this->call('createChatInviteLink', $parameters);
        return $response->getResult();
    }

    /**
     * 编辑聊天邀请链接
     */
    public function editChatInviteLink(
        int|string $chatId,
        string $inviteLink,
        array $options = []
    ): array {
        $this->validateChatId($chatId);
        $this->validateUrl($inviteLink);

        $parameters = $this->prepareParameters(array_merge([
            'chat_id' => $chatId,
            'invite_link' => $inviteLink,
        ], $options));

        $response = $this->call('editChatInviteLink', $parameters);
        return $response->getResult();
    }

    /**
     * 撤销聊天邀请链接
     */
    public function revokeChatInviteLink(
        int|string $chatId,
        string $inviteLink
    ): array {
        $this->validateChatId($chatId);
        $this->validateUrl($inviteLink);

        $parameters = $this->prepareParameters([
            'chat_id' => $chatId,
            'invite_link' => $inviteLink,
        ]);

        $response = $this->call('revokeChatInviteLink', $parameters);
        return $response->getResult();
    }

    /**
     * 批准聊天加入请求
     */
    public function approveChatJoinRequest(
        int|string $chatId,
        int $userId
    ): bool {
        $this->validateChatId($chatId);
        $this->validateUserId($userId);

        $parameters = $this->prepareParameters([
            'chat_id' => $chatId,
            'user_id' => $userId,
        ]);

        $response = $this->call('approveChatJoinRequest', $parameters);
        return (bool) $response->getResult();
    }

    /**
     * 拒绝聊天加入请求
     */
    public function declineChatJoinRequest(
        int|string $chatId,
        int $userId
    ): bool {
        $this->validateChatId($chatId);
        $this->validateUserId($userId);

        $parameters = $this->prepareParameters([
            'chat_id' => $chatId,
            'user_id' => $userId,
        ]);

        $response = $this->call('declineChatJoinRequest', $parameters);
        return (bool) $response->getResult();
    }

    /**
     * 设置聊天照片
     */
    public function setChatPhoto(
        int|string $chatId,
        string $photo
    ): bool {
        $this->validateChatId($chatId);

        $parameters = [
            'chat_id' => $chatId,
        ];

        $files = [];
        if ($this->isFilePath($photo)) {
            $files['photo'] = $photo;
        } else {
            $parameters['photo'] = $photo;
        }

        $parameters = $this->prepareParameters($parameters);

        if (!empty($files)) {
            $response = $this->upload('setChatPhoto', $parameters, $files);
        } else {
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

        $parameters = $this->prepareParameters([
            'chat_id' => $chatId,
        ]);

        $response = $this->call('deleteChatPhoto', $parameters);
        return (bool) $response->getResult();
    }

    /**
     * 设置聊天标题
     */
    public function setChatTitle(
        int|string $chatId,
        string $title
    ): bool {
        $this->validateChatId($chatId);

        if (empty($title)) {
            throw new \InvalidArgumentException('Title cannot be empty');
        }

        if (strlen($title) > 128) {
            throw new \InvalidArgumentException('Title cannot exceed 128 characters');
        }

        $parameters = $this->prepareParameters([
            'chat_id' => $chatId,
            'title' => $title,
        ]);

        $response = $this->call('setChatTitle', $parameters);
        return (bool) $response->getResult();
    }

    /**
     * 设置聊天描述
     */
    public function setChatDescription(
        int|string $chatId,
        string $description = ''
    ): bool {
        $this->validateChatId($chatId);

        if (strlen($description) > 255) {
            throw new \InvalidArgumentException('Description cannot exceed 255 characters');
        }

        $parameters = $this->prepareParameters([
            'chat_id' => $chatId,
            'description' => $description,
        ]);

        $response = $this->call('setChatDescription', $parameters);
        return (bool) $response->getResult();
    }

    /**
     * 固定聊天消息
     */
    public function pinChatMessage(
        int|string $chatId,
        int $messageId,
        bool $disableNotification = false
    ): bool {
        $this->validateChatId($chatId);
        $this->validateMessageId($messageId);

        $parameters = $this->prepareParameters([
            'chat_id' => $chatId,
            'message_id' => $messageId,
            'disable_notification' => $disableNotification,
        ]);

        $response = $this->call('pinChatMessage', $parameters);
        return (bool) $response->getResult();
    }

    /**
     * 取消固定聊天消息
     */
    public function unpinChatMessage(
        int|string $chatId,
        int $messageId = null
    ): bool {
        $this->validateChatId($chatId);

        if ($messageId !== null) {
            $this->validateMessageId($messageId);
        }

        $parameters = $this->prepareParameters([
            'chat_id' => $chatId,
            'message_id' => $messageId,
        ]);

        $response = $this->call('unpinChatMessage', $parameters);
        return (bool) $response->getResult();
    }

    /**
     * 取消固定所有聊天消息
     */
    public function unpinAllChatMessages(int|string $chatId): bool
    {
        $this->validateChatId($chatId);

        $parameters = $this->prepareParameters([
            'chat_id' => $chatId,
        ]);

        $response = $this->call('unpinAllChatMessages', $parameters);
        return (bool) $response->getResult();
    }

    /**
     * 离开聊天
     */
    public function leaveChat(int|string $chatId): bool
    {
        $this->validateChatId($chatId);

        $parameters = $this->prepareParameters([
            'chat_id' => $chatId,
        ]);

        $response = $this->call('leaveChat', $parameters);
        return (bool) $response->getResult();
    }

    /**
     * 设置聊天菜单按钮
     */
    public function setChatMenuButton(
        int|string $chatId = null,
        array $menuButton = null
    ): bool {
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
     * 创建论坛主题
     */
    public function createForumTopic(
        int|string $chatId,
        string $name,
        array $options = []
    ): array {
        $this->validateChatId($chatId);

        if (empty($name)) {
            throw new \InvalidArgumentException('Topic name cannot be empty');
        }

        if (strlen($name) > 128) {
            throw new \InvalidArgumentException('Topic name cannot exceed 128 characters');
        }

        $parameters = $this->prepareParameters(array_merge([
            'chat_id' => $chatId,
            'name' => $name,
        ], $options));

        $response = $this->call('createForumTopic', $parameters);
        return $response->getResult();
    }

    /**
     * 编辑论坛主题
     */
    public function editForumTopic(
        int|string $chatId,
        int $messageThreadId,
        array $options = []
    ): bool {
        $this->validateChatId($chatId);

        if ($messageThreadId <= 0) {
            throw new \InvalidArgumentException('Message thread ID must be a positive integer');
        }

        $parameters = $this->prepareParameters(array_merge([
            'chat_id' => $chatId,
            'message_thread_id' => $messageThreadId,
        ], $options));

        $response = $this->call('editForumTopic', $parameters);
        return (bool) $response->getResult();
    }

    /**
     * 关闭论坛主题
     */
    public function closeForumTopic(
        int|string $chatId,
        int $messageThreadId
    ): bool {
        $this->validateChatId($chatId);

        if ($messageThreadId <= 0) {
            throw new \InvalidArgumentException('Message thread ID must be a positive integer');
        }

        $parameters = $this->prepareParameters([
            'chat_id' => $chatId,
            'message_thread_id' => $messageThreadId,
        ]);

        $response = $this->call('closeForumTopic', $parameters);
        return (bool) $response->getResult();
    }

    /**
     * 重新打开论坛主题
     */
    public function reopenForumTopic(
        int|string $chatId,
        int $messageThreadId
    ): bool {
        $this->validateChatId($chatId);

        if ($messageThreadId <= 0) {
            throw new \InvalidArgumentException('Message thread ID must be a positive integer');
        }

        $parameters = $this->prepareParameters([
            'chat_id' => $chatId,
            'message_thread_id' => $messageThreadId,
        ]);

        $response = $this->call('reopenForumTopic', $parameters);
        return (bool) $response->getResult();
    }

    /**
     * 删除论坛主题
     */
    public function deleteForumTopic(
        int|string $chatId,
        int $messageThreadId
    ): bool {
        $this->validateChatId($chatId);

        if ($messageThreadId <= 0) {
            throw new \InvalidArgumentException('Message thread ID must be a positive integer');
        }

        $parameters = $this->prepareParameters([
            'chat_id' => $chatId,
            'message_thread_id' => $messageThreadId,
        ]);

        $response = $this->call('deleteForumTopic', $parameters);
        return (bool) $response->getResult();
    }

    /**
     * 取消固定所有论坛主题消息
     */
    public function unpinAllForumTopicMessages(
        int|string $chatId,
        int $messageThreadId
    ): bool {
        $this->validateChatId($chatId);

        if ($messageThreadId <= 0) {
            throw new \InvalidArgumentException('Message thread ID must be a positive integer');
        }

        $parameters = $this->prepareParameters([
            'chat_id' => $chatId,
            'message_thread_id' => $messageThreadId,
        ]);

        $response = $this->call('unpinAllForumTopicMessages', $parameters);
        return (bool) $response->getResult();
    }

    /**
     * 编辑常规论坛主题
     */
    public function editGeneralForumTopic(
        int|string $chatId,
        string $name
    ): bool {
        $this->validateChatId($chatId);

        if (empty($name)) {
            throw new \InvalidArgumentException('Topic name cannot be empty');
        }

        if (strlen($name) > 128) {
            throw new \InvalidArgumentException('Topic name cannot exceed 128 characters');
        }

        $parameters = $this->prepareParameters([
            'chat_id' => $chatId,
            'name' => $name,
        ]);

        $response = $this->call('editGeneralForumTopic', $parameters);
        return (bool) $response->getResult();
    }

    /**
     * 关闭常规论坛主题
     */
    public function closeGeneralForumTopic(int|string $chatId): bool
    {
        $this->validateChatId($chatId);

        $parameters = $this->prepareParameters([
            'chat_id' => $chatId,
        ]);

        $response = $this->call('closeGeneralForumTopic', $parameters);
        return (bool) $response->getResult();
    }

    /**
     * 重新打开常规论坛主题
     */
    public function reopenGeneralForumTopic(int|string $chatId): bool
    {
        $this->validateChatId($chatId);

        $parameters = $this->prepareParameters([
            'chat_id' => $chatId,
        ]);

        $response = $this->call('reopenGeneralForumTopic', $parameters);
        return (bool) $response->getResult();
    }

    /**
     * 隐藏常规论坛主题
     */
    public function hideGeneralForumTopic(int|string $chatId): bool
    {
        $this->validateChatId($chatId);

        $parameters = $this->prepareParameters([
            'chat_id' => $chatId,
        ]);

        $response = $this->call('hideGeneralForumTopic', $parameters);
        return (bool) $response->getResult();
    }

    /**
     * 取消隐藏常规论坛主题
     */
    public function unhideGeneralForumTopic(int|string $chatId): bool
    {
        $this->validateChatId($chatId);

        $parameters = $this->prepareParameters([
            'chat_id' => $chatId,
        ]);

        $response = $this->call('unhideGeneralForumTopic', $parameters);
        return (bool) $response->getResult();
    }
}
