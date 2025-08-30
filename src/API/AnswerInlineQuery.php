<?php

declare(strict_types=1);

namespace XBot\Telegram\API;

final class AnswerInlineQuery extends BaseEndpoint
{
    public function __invoke(string $inlineQueryId, array $results, array $options = []): bool
    {
        $parameters = $this->prepareParameters(array_merge([
            'inline_query_id' => $inlineQueryId,
            'results'         => $results,
        ], $options));

        $response = $this->call('answerInlineQuery', $parameters);
        return (bool) $response->getResult();
    }
}

