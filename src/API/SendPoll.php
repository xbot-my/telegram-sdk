<?php

declare(strict_types=1);

namespace XBot\Telegram\API;

final class SendPoll extends BaseEndpoint
{
    public function __invoke(int|string $chatId, string $question, array $optionsList, array $options = []): \XBot\Telegram\Http\Response\Transformer
    {
        $this->validateChatId($chatId);
        $this->validateRequired([
            'question' => $question,
            'options'  => $optionsList,
        ], ['question', 'options']);

        $parameters = array_merge([
            'chat_id'  => $chatId,
            'question' => $question,
            'options'  => $optionsList,
        ], $options);

        $parameters = $this->prepareParameters($parameters);
        $response = $this->call('sendPoll', $parameters)->ensureOk();
        return $this->formatResult($response->getResult());
    }
}

