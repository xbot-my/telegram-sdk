<?php

declare(strict_types=1);

namespace XBot\Telegram\API;

final class CreateInvoiceLink extends BaseEndpoint
{
    public function __invoke(
        string $title,
        string $description,
        string $payload,
        string $providerToken,
        string $currency,
        array $prices,
        array $options = []
    ): string {
        $this->validateRequired(compact('title','description','payload','providerToken','currency','prices'), ['title','description','payload','providerToken','currency','prices']);

        $parameters = array_merge([
            'title'          => $title,
            'description'    => $description,
            'payload'        => $payload,
            'provider_token' => $providerToken,
            'currency'       => $currency,
            'prices'         => $prices,
        ], $options);

        $parameters = $this->prepareParameters($parameters);
        $response = $this->call('createInvoiceLink', $parameters)->ensureOk();
        return (string)$response->getResult();
    }
}

