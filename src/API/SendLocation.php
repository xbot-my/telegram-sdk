<?php

declare(strict_types=1);

namespace XBot\Telegram\API;

final class SendLocation extends BaseEndpoint
{
    public function __invoke(int|string $chatId, float $latitude, float $longitude, array $options = []): \XBot\Telegram\Http\Response\Transformer
    {
        $this->validateChatId($chatId);
        $this->validateCoordinates($latitude, $longitude);

        $parameters = $this->prepareParameters(array_merge([
            'chat_id'  => $chatId,
            'latitude' => $latitude,
            'longitude'=> $longitude,
        ], $options));

        $response = $this->call('sendLocation', $parameters)->ensureOk();
        return $this->formatResult($response->getResult());
    }
}

