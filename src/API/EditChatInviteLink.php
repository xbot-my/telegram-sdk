<?php

declare(strict_types=1);

namespace XBot\Telegram\API;

final class EditChatInviteLink extends BaseEndpoint
{
    public function __invoke(int|string $chatId, string $inviteLink, array $options = []): \XBot\Telegram\Http\Response\Transformer
    {
        $this->validateChatId($chatId);

        $parameters = $this->prepareParameters(array_merge([
            'chat_id'     => $chatId,
            'invite_link' => $inviteLink,
        ], $options));

        $response = $this->call('editChatInviteLink', $parameters)->ensureOk();
        return $this->formatResult($response->getResult());
    }
}
