<?php

declare(strict_types=1);

namespace XBot\Telegram\API;

final class SendDocument extends BaseEndpoint
{
    public function __invoke(int|string $chatId, string $document, array $options = []): \XBot\Telegram\Http\Response\Transformer
    {
        $this->validateChatId($chatId);

        $parameters = array_merge([
            'chat_id'  => $chatId,
            'document' => $document,
        ], $options);

        $files = $this->extractFiles($parameters);
        $parameters = $this->prepareParameters($parameters);

        $response = !empty($files)
            ? $this->upload('sendDocument', $parameters, $files)
            : $this->call('sendDocument', $parameters);

        $response->ensureOk();
        return $this->formatResult($response->getResult());
    }
}

