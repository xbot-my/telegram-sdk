<?php

declare(strict_types=1);

namespace XBot\Telegram\API;

final class SetChatPermissions extends BaseEndpoint
{
    public function __invoke(int|string $chatId, array $permissions, array $options = []): bool
    {
        $this->validateChatId($chatId);
        $this->validateRequired(['permissions' => $permissions], ['permissions']);

        $parameters = array_merge([
            'chat_id'     => $chatId,
            'permissions' => $permissions,
        ], $options);

        $parameters = $this->prepareParameters($parameters);
        $response = $this->call('setChatPermissions', $parameters);
        return (bool) $response->getResult();
    }
}
