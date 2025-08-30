<?php

declare(strict_types=1);

namespace XBot\Telegram\API;

final class AnswerPreCheckoutQuery extends BaseEndpoint
{
    public function __invoke(string $preCheckoutQueryId, bool $ok, ?string $errorMessage = null): bool
    {
        $this->validateRequired(['pre_checkout_query_id' => $preCheckoutQueryId], ['pre_checkout_query_id']);

        $parameters = $this->prepareParameters([
            'pre_checkout_query_id' => $preCheckoutQueryId,
            'ok'                    => $ok,
            'error_message'        => $errorMessage,
        ]);

        $response = $this->call('answerPreCheckoutQuery', $parameters);
        return (bool)$response->getResult();
    }
}

