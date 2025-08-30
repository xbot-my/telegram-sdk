<?php

declare(strict_types=1);

namespace XBot\Telegram\API;

final class SendMediaGroup extends BaseEndpoint
{
    public function __invoke(int|string $chatId, array $media, array $options = []): \XBot\Telegram\Http\Response\Transformer
    {
        $this->validateChatId($chatId);
        if (empty($media)) {
            throw new \InvalidArgumentException('Media array cannot be empty');
        }
        if (count($media) > 10) {
            throw new \InvalidArgumentException('Media array cannot contain more than 10 items');
        }

        $parameters = array_merge([
            'chat_id' => $chatId,
            'media'   => $media,
        ], $options);

        $files = $this->extractFiles($parameters);
        $parameters = $this->prepareParameters($parameters);

        $response = !empty($files)
            ? $this->upload('sendMediaGroup', $parameters, $files)
            : $this->call('sendMediaGroup', $parameters);

        $response->ensureOk();
        return $this->formatResult($response->getResult());
    }
}

