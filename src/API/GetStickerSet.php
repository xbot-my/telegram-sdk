<?php

declare(strict_types=1);

namespace XBot\Telegram\API;

final class GetStickerSet extends BaseEndpoint
{
    public function __invoke(string $name): \XBot\Telegram\Http\Response\Transformer
    {
        $this->validateRequired(['name' => $name], ['name']);

        $parameters = $this->prepareParameters([
            'name' => $name,
        ]);

        $response = $this->call('getStickerSet', $parameters)->ensureOk();
        return $this->formatResult($response->getResult());
    }
}

