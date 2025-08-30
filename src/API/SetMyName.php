<?php

declare(strict_types=1);

namespace XBot\Telegram\API;

final class SetMyName extends BaseEndpoint
{
    public function __invoke(?string $name = null, ?string $languageCode = null): bool
    {
        $parameters = $this->prepareParameters([
            'name' => $name,
            'language_code' => $languageCode,
        ]);
        $response = $this->call('setMyName', $parameters);
        return (bool) $response->getResult();
    }
}

