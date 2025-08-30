<?php

declare(strict_types=1);

namespace XBot\Telegram\API;

final class AnswerCallbackQuery extends BaseEndpoint
{
    public function __invoke(string $callbackQueryId, array $options = []): bool
    {
        $parameters = $this->prepareParameters(array_merge([
            'callback_query_id' => $callbackQueryId,
        ], $options));

        $response = $this->call('answerCallbackQuery', $parameters);
        return (bool) $response->getResult();
    }
}

