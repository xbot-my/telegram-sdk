<?php

declare(strict_types=1);

namespace XBot\Telegram\API;

final class DeleteStickerFromSet extends BaseEndpoint
{
    public function __invoke(string $sticker): bool
    {
        $this->validateRequired(['sticker' => $sticker], ['sticker']);

        $parameters = $this->prepareParameters([
            'sticker' => $sticker,
        ]);

        $response = $this->call('deleteStickerFromSet', $parameters);
        return (bool) $response->getResult();
    }
}
