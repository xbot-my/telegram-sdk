<?php

declare(strict_types=1);

namespace XBot\Telegram\API;

final class GetWebhookInfo extends BaseEndpoint
{
    public function __invoke(): \XBot\Telegram\Http\Response\Transformer
    {
        $response = $this->call('getWebhookInfo')->ensureOk();
        return $this->formatResult($response->getResult());
    }
}
