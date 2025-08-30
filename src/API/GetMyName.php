<?php

declare(strict_types=1);

namespace XBot\Telegram\API;

final class GetMyName extends BaseEndpoint
{
    public function __invoke(?string $languageCode = null): \XBot\Telegram\Http\Response\Transformer
    {
        $parameters = $this->prepareParameters([
            'language_code' => $languageCode,
        ]);
        $response = $this->call('getMyName', $parameters)->ensureOk();
        return $this->formatResult($response->getResult());
    }
}

