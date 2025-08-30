<?php

declare(strict_types=1);

namespace XBot\Telegram\API;

final class SendContact extends BaseEndpoint
{
    public function __invoke(int|string $chatId, string $phoneNumber, string $firstName, array $options = []): \XBot\Telegram\Http\Response\Transformer
    {
        $this->validateChatId($chatId);
        $this->validateRequired([
            'phone_number' => $phoneNumber,
            'first_name'   => $firstName,
        ], ['phone_number', 'first_name']);

        $parameters = array_merge([
            'chat_id'      => $chatId,
            'phone_number' => $phoneNumber,
            'first_name'   => $firstName,
        ], $options);

        $parameters = $this->prepareParameters($parameters);
        $response = $this->call('sendContact', $parameters)->ensureOk();
        return $this->formatResult($response->getResult());
    }
}

