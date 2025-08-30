<?php

declare(strict_types=1);

namespace XBot\Telegram\API;

final class GetUserProfilePhotos extends BaseEndpoint
{
    public function __invoke(int $userId, array $options = []): \XBot\Telegram\Http\Response\Transformer
    {
        $this->validateUserId($userId);

        $parameters = array_merge([
            'user_id' => $userId,
        ], $options);

        $parameters = $this->prepareParameters($parameters);
        $response = $this->call('getUserProfilePhotos', $parameters)->ensureOk();
        return $this->formatResult($response->getResult());
    }
}

