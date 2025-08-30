<?php

declare(strict_types=1);

namespace XBot\Telegram\API;

final class SetMyDefaultAdministratorRights extends BaseEndpoint
{
    public function __invoke(?array $rights = null, ?bool $forChannels = null): bool
    {
        $parameters = $this->prepareParameters([
            'rights' => $rights,
            'for_channels' => $forChannels,
        ]);
        $response = $this->call('setMyDefaultAdministratorRights', $parameters);
        return (bool) $response->getResult();
    }
}

