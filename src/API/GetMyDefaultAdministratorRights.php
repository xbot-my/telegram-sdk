<?php

declare(strict_types=1);

namespace XBot\Telegram\API;

final class GetMyDefaultAdministratorRights extends BaseEndpoint
{
    public function __invoke(?bool $forChannels = null): \XBot\Telegram\Http\Response\Transformer
    {
        $parameters = $this->prepareParameters([
            'for_channels' => $forChannels,
        ]);
        $response = $this->call('getMyDefaultAdministratorRights', $parameters)->ensureOk();
        return $this->formatResult($response->getResult());
    }
}

