<?php

declare(strict_types=1);

namespace XBot\Telegram\API;

final class UploadStickerFile extends BaseEndpoint
{
    public function __invoke(int $userId, $sticker, string $stickerFormat): \XBot\Telegram\Http\Response\Transformer
    {
        $this->validateUserId($userId);
        $this->validateRequired(['sticker' => $sticker, 'sticker_format' => $stickerFormat], ['sticker', 'sticker_format']);

        $parameters = [
            'user_id'        => $userId,
            'sticker'        => $sticker,
            'sticker_format' => $stickerFormat,
        ];

        $files = $this->extractFiles($parameters);
        $parameters = $this->prepareParameters($parameters);

        $response = $this->upload('uploadStickerFile', $parameters, $files)->ensureOk();
        return $this->formatResult($response->getResult());
    }
}

