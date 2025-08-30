<?php

declare(strict_types=1);

namespace XBot\Telegram\API;

final class EditMessageCaption extends BaseEndpoint
{
    public function __invoke(array $params): \XBot\Telegram\Http\Response\Transformer
    {
        // Requires either (chat_id, message_id) or inline_message_id
        $parameters = $this->prepareParameters($params);
        $response = $this->call('editMessageCaption', $parameters)->ensureOk();
        return $this->formatResult($response->getResult());
    }
}

