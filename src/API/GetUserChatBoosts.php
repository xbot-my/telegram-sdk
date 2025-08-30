<?php

declare(strict_types=1);

namespace XBot\Telegram\API;

final class GetUserChatBoosts extends BaseEndpoint
{
    public function __invoke(int|string $chatId, int $userId): \XBot\Telegram\Http\Response\Transformer
    {
        $this->validateChatId($chatId);
        $this->validateUserId($userId);
        $parameters = $this->prepareParameters([
            'chat_id' => $chatId,
            'user_id' => $userId,
        ]);
        $response = $this->call('getUserChatBoosts', $parameters)->ensureOk();
        return $this->formatResult($response->getResult());
    }
}

