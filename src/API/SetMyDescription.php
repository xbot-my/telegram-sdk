<?php

declare(strict_types=1);

namespace XBot\Telegram\API;

final class SetMyDescription extends BaseEndpoint
{
    public function __invoke(?string $description = null, ?string $languageCode = null): bool
    {
        $parameters = $this->prepareParameters([
            'description' => $description,
            'language_code' => $languageCode,
        ]);
        $response = $this->call('setMyDescription', $parameters);
        return (bool) $response->getResult();
    }
}

