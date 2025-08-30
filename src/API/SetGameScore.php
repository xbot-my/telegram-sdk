<?php

declare(strict_types=1);

namespace XBot\Telegram\API;

final class SetGameScore extends BaseEndpoint
{
    public function __invoke(int $userId, int $score, array $params = []): \XBot\Telegram\Http\Response\Transformer
    {
        $this->validateUserId($userId);
        $this->validateRequired(['score' => $score], ['score']);
        $parameters = array_merge(['user_id' => $userId, 'score' => $score], $params);
        $parameters = $this->prepareParameters($parameters);
        $response = $this->call('setGameScore', $parameters)->ensureOk();
        return $this->formatResult($response->getResult());
    }
}

