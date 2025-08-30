<?php

declare(strict_types=1);

namespace XBot\Telegram\API;

final class SendVenue extends BaseEndpoint
{
    public function __invoke(int|string $chatId, float $latitude, float $longitude, string $title, string $address, array $options = []): \XBot\Telegram\Http\Response\Transformer
    {
        $this->validateChatId($chatId);
        $this->validateCoordinates($latitude, $longitude);
        $this->validateTextLength($title, 255);
        $this->validateTextLength($address, 512);

        $parameters = $this->prepareParameters(array_merge([
            'chat_id'  => $chatId,
            'latitude' => $latitude,
            'longitude'=> $longitude,
            'title'    => $title,
            'address'  => $address,
        ], $options));

        $response = $this->call('sendVenue', $parameters)->ensureOk();
        return $this->formatResult($response->getResult());
    }
}

