<?php

declare(strict_types=1);

namespace XBot\Telegram\API;

final class DeleteWebhook extends BaseEndpoint
{
    public function __invoke(bool $dropPendingUpdates = false): bool
    {
        $parameters = $this->prepareParameters([
            'drop_pending_updates' => $dropPendingUpdates,
        ]);

        $response = $this->call('deleteWebhook', $parameters);
        return (bool) $response->getResult();
    }
}

