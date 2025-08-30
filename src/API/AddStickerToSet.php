<?php

declare(strict_types=1);

namespace XBot\Telegram\API;

final class AddStickerToSet extends BaseEndpoint
{
    public function __invoke(int $userId, string $name, array $sticker): bool
    {
        $this->validateUserId($userId);
        $this->validateRequired([
            'name'    => $name,
            'sticker' => $sticker,
        ], ['name', 'sticker']);

        $parameters = [
            'user_id' => $userId,
            'name'    => $name,
            'sticker' => $sticker,
        ];

        $files = $this->extractFiles($parameters);
        $parameters = $this->prepareParameters($parameters);

        $response = !empty($files)
            ? $this->upload('addStickerToSet', $parameters, $files)
            : $this->call('addStickerToSet', $parameters);

        return (bool) $response->getResult();
    }
}
