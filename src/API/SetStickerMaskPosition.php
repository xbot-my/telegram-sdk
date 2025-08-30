<?php

declare(strict_types=1);

namespace XBot\Telegram\API;

final class SetStickerMaskPosition extends BaseEndpoint
{
    public function __invoke(string $sticker, $maskPosition = null): bool
    {
        $this->validateRequired(['sticker' => $sticker], ['sticker']);

        $parameters = $this->prepareParameters([
            'sticker'       => $sticker,
            'mask_position' => $maskPosition,
        ]);

        $response = $this->call('setStickerMaskPosition', $parameters);
        return (bool) $response->getResult();
    }
}
