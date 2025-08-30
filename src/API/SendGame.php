<?php

declare(strict_types=1);

namespace XBot\Telegram\API;

final class SendGame extends BaseEndpoint
{
    public function __invoke(int $chatId, string $gameShortName, array $options = []): \XBot\Telegram\Http\Response\Transformer
    {
        $this->validateRequired(['chat_id' => $chatId, 'game_short_name' => $gameShortName], ['chat_id','game_short_name']);
        $parameters = array_merge(['chat_id' => $chatId, 'game_short_name' => $gameShortName], $options);
        $parameters = $this->prepareParameters($parameters);
        $response = $this->call('sendGame', $parameters)->ensureOk();
        return $this->formatResult($response->getResult());
    }
}

