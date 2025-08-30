<?php

declare(strict_types=1);

namespace XBot\Telegram\API;

final class GetFile extends BaseEndpoint
{
    public function __invoke(string $fileId): \XBot\Telegram\Http\Response\Transformer
    {
        $parameters = $this->prepareParameters([
            'file_id' => $fileId,
        ]);

        $response = $this->call('getFile', $parameters)->ensureOk();
        return $this->formatResult($response->getResult());
    }
}
