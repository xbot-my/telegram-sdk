<?php

declare(strict_types=1);

namespace XBot\Telegram\API;

final class CreateNewStickerSet extends BaseEndpoint
{
    public function __invoke(int $userId, string $name, string $title, array $stickers, array $options = []): bool
    {
        $this->validateUserId($userId);
        $this->validateRequired([
            'name'     => $name,
            'title'    => $title,
            'stickers' => $stickers,
        ], ['name', 'title', 'stickers']);

        $parameters = array_merge([
            'user_id' => $userId,
            'name'    => $name,
            'title'   => $title,
            'stickers'=> $stickers,
        ], $options);

        $files = $this->extractFiles($parameters);
        $parameters = $this->prepareParameters($parameters);

        $response = !empty($files)
            ? $this->upload('createNewStickerSet', $parameters, $files)
            : $this->call('createNewStickerSet', $parameters);

        return (bool) $response->getResult();
    }
}
