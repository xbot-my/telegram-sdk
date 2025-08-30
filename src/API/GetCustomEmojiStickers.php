<?php

declare(strict_types=1);

namespace XBot\Telegram\API;

final class GetCustomEmojiStickers extends BaseEndpoint
{
    public function __invoke(array $customEmojiIds): \XBot\Telegram\Http\Response\Transformer
    {
        $this->validateRequired(['custom_emoji_ids' => $customEmojiIds], ['custom_emoji_ids']);

        $parameters = $this->prepareParameters([
            'custom_emoji_ids' => $customEmojiIds,
        ]);

        $response = $this->call('getCustomEmojiStickers', $parameters)->ensureOk();
        return $this->formatResult($response->getResult());
    }
}

