<?php

declare(strict_types=1);

namespace XBot\Telegram\API;

final class SetStickerKeywords extends BaseEndpoint
{
    public function __invoke(string $sticker, ?array $keywords): bool
    {
        $this->validateRequired(['sticker' => $sticker], ['sticker']);

        $parameters = $this->prepareParameters([
            'sticker'  => $sticker,
            'keywords' => $keywords,
        ]);

        $response = $this->call('setStickerKeywords', $parameters);
        return (bool) $response->getResult();
    }
}
