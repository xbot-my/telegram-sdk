<?php

declare(strict_types=1);

namespace XBot\Telegram\API;

final class CreateForumTopic extends BaseEndpoint
{
    public function __invoke(int|string $chatId, string $name, array $options = []): \XBot\Telegram\Http\Response\Transformer
    {
        $this->validateChatId($chatId);
        $this->validateRequired(['name' => $name], ['name']);

        $parameters = array_merge([
            'chat_id' => $chatId,
            'name'    => $name,
        ], $options);

        $parameters = $this->prepareParameters($parameters);
        $response = $this->call('createForumTopic', $parameters)->ensureOk();
        return $this->formatResult($response->getResult());
    }
}

