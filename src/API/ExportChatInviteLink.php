<?php

declare(strict_types=1);

namespace XBot\Telegram\API;

final class ExportChatInviteLink extends BaseEndpoint
{
    public function __invoke(int|string $chatId): string
    {
        $this->validateChatId($chatId);
        $parameters = $this->prepareParameters(['chat_id' => $chatId]);
        $response = $this->call('exportChatInviteLink', $parameters)->ensureOk();
        return (string)$response->getResult();
    }
}

