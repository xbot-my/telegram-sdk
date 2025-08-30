<?php

declare(strict_types=1);

namespace XBot\Telegram\API;

final class AnswerShippingQuery extends BaseEndpoint
{
    public function __invoke(string $shippingQueryId, bool $ok, array $options = []): bool
    {
        $this->validateRequired(['shipping_query_id' => $shippingQueryId], ['shipping_query_id']);

        $parameters = array_merge([
            'shipping_query_id' => $shippingQueryId,
            'ok'                => $ok,
        ], $options);

        $parameters = $this->prepareParameters($parameters);
        $response = $this->call('answerShippingQuery', $parameters);
        return (bool)$response->getResult();
    }
}

