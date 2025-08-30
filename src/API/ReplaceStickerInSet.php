<?php

declare(strict_types=1);

namespace XBot\Telegram\API;

final class ReplaceStickerInSet extends BaseEndpoint
{
    public function __invoke(int $userId, string $name, string $oldSticker, array $sticker): bool
    {
        $this->validateUserId($userId);
        $this->validateRequired([
            'name'        => $name,
            'old_sticker' => $oldSticker,
            'sticker'     => $sticker,
        ], ['name', 'old_sticker', 'sticker']);

        $parameters = [
            'user_id'     => $userId,
            'name'        => $name,
            'old_sticker' => $oldSticker,
            'sticker'     => $sticker,
        ];

        $files = $this->extractFiles($parameters);
        $parameters = $this->prepareParameters($parameters);

        $response = !empty($files)
            ? $this->upload('replaceStickerInSet', $parameters, $files)
            : $this->call('replaceStickerInSet', $parameters);

        return (bool) $response->getResult();
    }
}
