<?php

declare(strict_types=1);

namespace XBot\Telegram\API;

use XBot\Telegram\Http\Response\Transformer;

final class GetMe extends BaseEndpoint
{
    public function __invoke(...$args): Transformer
    {
        $response = $this->call('getMe')->ensureOk();

        return $this->formatResult($response->getResult());
    }
}
