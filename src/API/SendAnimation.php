<?php

declare(strict_types=1);

namespace XBot\Telegram\API;

final class SendAnimation extends BaseEndpoint
{
    public function __invoke(int|string $chatId, string $animation, array $options = []): \XBot\Telegram\Http\Response\Transformer
    {
        $this->validateChatId($chatId);

        $parameters = array_merge([
            'chat_id'   => $chatId,
            'animation' => $animation,
        ], $options);

        $files = $this->extractFiles($parameters);
        $parameters = $this->prepareParameters($parameters);

        $response = !empty($files)
            ? $this->upload('sendAnimation', $parameters, $files)
            : $this->call('sendAnimation', $parameters);

        $response->ensureOk();
        return $this->formatResult($response->getResult());
    }
}

