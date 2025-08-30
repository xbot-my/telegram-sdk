<?php

declare(strict_types=1);

namespace XBot\Telegram\API;

final class SendInvoice extends BaseEndpoint
{
    public function __invoke(
        int|string $chatId,
        string $title,
        string $description,
        string $payload,
        string $providerToken,
        string $currency,
        array $prices,
        array $options = []
    ): \XBot\Telegram\Http\Response\Transformer {
        $this->validateChatId($chatId);
        $this->validateRequired(compact('title','description','payload','providerToken','currency','prices'), ['title','description','payload','providerToken','currency','prices']);

        $parameters = array_merge([
            'chat_id'        => $chatId,
            'title'          => $title,
            'description'    => $description,
            'payload'        => $payload,
            'provider_token' => $providerToken,
            'currency'       => $currency,
            'prices'         => $prices,
        ], $options);

        $parameters = $this->prepareParameters($parameters);
        $response = $this->call('sendInvoice', $parameters)->ensureOk();
        return $this->formatResult($response->getResult());
    }
}

