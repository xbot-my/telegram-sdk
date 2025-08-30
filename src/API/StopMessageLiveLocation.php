<?php

declare(strict_types=1);

namespace XBot\Telegram\API;

final class StopMessageLiveLocation extends BaseEndpoint
{
    public function __invoke(array $params = []): \XBot\Telegram\Http\Response\Transformer
    {
        $parameters = $this->prepareParameters($params);
        $response = $this->call('stopMessageLiveLocation', $parameters)->ensureOk();
        return $this->formatResult($response->getResult());
    }
}

