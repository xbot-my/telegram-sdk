<?php

declare(strict_types=1);

namespace XBot\Telegram\API;

final class GetGameHighScores extends BaseEndpoint
{
    public function __invoke(int $userId, array $params = []): \XBot\Telegram\Http\Response\Transformer
    {
        $this->validateUserId($userId);
        $parameters = array_merge(['user_id' => $userId], $params);
        $parameters = $this->prepareParameters($parameters);
        $response = $this->call('getGameHighScores', $parameters)->ensureOk();
        return $this->formatResult($response->getResult());
    }
}

