<?php

declare(strict_types=1);

namespace XBot\Telegram\API;

final class GetMyShortDescription extends BaseEndpoint
{
    public function __invoke(?string $languageCode = null): \XBot\Telegram\Http\Response\Transformer
    {
        $parameters = $this->prepareParameters([
            'language_code' => $languageCode,
        ]);
        $response = $this->call('getMyShortDescription', $parameters)->ensureOk();
        return $this->formatResult($response->getResult());
    }
}

