<?php

declare(strict_types=1);

namespace XBot\Telegram\API;

final class EditMessageLiveLocation extends BaseEndpoint
{
    public function __invoke(float $latitude, float $longitude, array $params = []): \XBot\Telegram\Http\Response\Transformer
    {
        $this->validateCoordinates($latitude, $longitude);
        $parameters = array_merge(['latitude' => $latitude, 'longitude' => $longitude], $params);
        $parameters = $this->prepareParameters($parameters);
        $response = $this->call('editMessageLiveLocation', $parameters)->ensureOk();
        return $this->formatResult($response->getResult());
    }
}

