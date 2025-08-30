<?php

declare(strict_types=1);

namespace XBot\Telegram\API;

final class RevokeChatInviteLink extends BaseEndpoint
{
    public function __invoke(int|string $chatId, string $inviteLink): \XBot\Telegram\Http\Response\Transformer
    {
        $this->validateChatId($chatId);

        $parameters = $this->prepareParameters([
            'chat_id'     => $chatId,
            'invite_link' => $inviteLink,
        ]);

        $response = $this->call('revokeChatInviteLink', $parameters)->ensureOk();
        return $this->formatResult($response->getResult());
    }
}
