<?php

declare(strict_types=1);

namespace XBot\Telegram\API;

final class GetMyCommands extends BaseEndpoint
{
    public function __invoke(array $options = []): \XBot\Telegram\Http\Response\Transformer
    {
        $parameters = $this->prepareParameters($options);
        $response = $this->call('getMyCommands', $parameters)->ensureOk();
        return $this->formatResult($response->getResult());
    }
}

