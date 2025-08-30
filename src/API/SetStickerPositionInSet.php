<?php

declare(strict_types=1);

namespace XBot\Telegram\API;

final class SetStickerPositionInSet extends BaseEndpoint
{
    public function __invoke(string $sticker, int $position): bool
    {
        $this->validateRequired(['sticker' => $sticker], ['sticker']);
        $parameters = $this->prepareParameters(['sticker' => $sticker, 'position' => $position]);
        $response = $this->call('setStickerPositionInSet', $parameters);
        return (bool)$response->getResult();
    }
}

