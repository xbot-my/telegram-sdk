<?php

declare(strict_types=1);

namespace XBot\Telegram\API;

final class SetWebhook extends BaseEndpoint
{
    public function __invoke(string $url, array $options = []): bool
    {
        $this->validateUrl($url, true);

        $parameters = $this->prepareParameters(array_merge([
            'url' => $url,
        ], $options));

        $response = $this->call('setWebhook', $parameters);
        return (bool) $response->getResult();
    }
}

