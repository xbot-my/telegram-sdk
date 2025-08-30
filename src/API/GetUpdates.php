<?php

declare(strict_types=1);

namespace XBot\Telegram\API;

final class GetUpdates extends BaseEndpoint
{
    public function __invoke(array $options = []): \XBot\Telegram\Http\Response\Transformer
    {
        $parameters = $this->prepareParameters($options);
        $response = $this->call('getUpdates', $parameters)->ensureOk();
        return $this->formatResult($response->getResult());
    }
}

