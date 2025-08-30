<?php

declare(strict_types=1);

namespace XBot\Telegram\API;

final class SetMyShortDescription extends BaseEndpoint
{
    public function __invoke(?string $shortDescription = null, ?string $languageCode = null): bool
    {
        $parameters = $this->prepareParameters([
            'short_description' => $shortDescription,
            'language_code' => $languageCode,
        ]);
        $response = $this->call('setMyShortDescription', $parameters);
        return (bool) $response->getResult();
    }
}

