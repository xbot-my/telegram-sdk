<?php

declare(strict_types=1);

namespace XBot\Telegram\API;

final class SetStickerSetTitle extends BaseEndpoint
{
    public function __invoke(string $name, string $title): bool
    {
        $this->validateRequired(['name' => $name, 'title' => $title], ['name', 'title']);

        $parameters = $this->prepareParameters([
            'name'  => $name,
            'title' => $title,
        ]);

        $response = $this->call('setStickerSetTitle', $parameters);
        return (bool) $response->getResult();
    }
}
