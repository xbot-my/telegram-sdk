<?php

declare(strict_types=1);

namespace XBot\Telegram\API;

final class GetMe extends BaseEndpoint
{
    public function __invoke(): \XBot\Telegram\Http\Response\Transformer
    {
        $response = $this->call('getMe')->ensureOk();
        return $this->formatResult($response->getResult());
    }
}
