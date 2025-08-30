<?php

declare(strict_types=1);

namespace XBot\Telegram\API;

final class GetForumTopicIconStickers extends BaseEndpoint
{
    public function __invoke(): \XBot\Telegram\Http\Response\Transformer
    {
        $response = $this->call('getForumTopicIconStickers')->ensureOk();
        return $this->formatResult($response->getResult());
    }
}

