<?php

declare(strict_types=1);

namespace XBot\Telegram\API;

final class AnswerWebAppQuery extends BaseEndpoint
{
    public function __invoke(string $webAppQueryId, array $result): \XBot\Telegram\Http\Response\Transformer
    {
        $this->validateRequired([
            'web_app_query_id' => $webAppQueryId,
            'result' => $result,
        ], ['web_app_query_id','result']);

        $parameters = $this->prepareParameters([
            'web_app_query_id' => $webAppQueryId,
            'result' => $result,
        ]);

        $response = $this->call('answerWebAppQuery', $parameters)->ensureOk();
        return $this->formatResult($response->getResult());
    }
}

