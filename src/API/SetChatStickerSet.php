<?php

declare(strict_types=1);

namespace XBot\Telegram\API;

final class SetChatStickerSet extends BaseEndpoint
{
    public function __invoke(int|string $chatId, string $stickerSetName): bool
    {
        $this->validateChatId($chatId);
        $this->validateRequired(['sticker_set_name' => $stickerSetName], ['sticker_set_name']);
        $parameters = $this->prepareParameters(['chat_id' => $chatId, 'sticker_set_name' => $stickerSetName]);
        $response = $this->call('setChatStickerSet', $parameters);
        return (bool)$response->getResult();
    }
}

