<?php

declare(strict_types=1);

namespace XBot\Telegram\API;

final class SetStickerEmojiList extends BaseEndpoint
{
    public function __invoke(string $sticker, array $emojiList): bool
    {
        $this->validateRequired([
            'sticker'    => $sticker,
            'emoji_list' => $emojiList,
        ], ['sticker', 'emoji_list']);

        $parameters = $this->prepareParameters([
            'sticker'    => $sticker,
            'emoji_list' => $emojiList,
        ]);

        $response = $this->call('setStickerEmojiList', $parameters);
        return (bool) $response->getResult();
    }
}
