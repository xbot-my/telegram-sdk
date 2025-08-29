<?php

declare(strict_types=1);

use XBot\Telegram\Contracts\HttpClientInterface;
use XBot\Telegram\Methods\BaseMethodGroup;
use XBot\Telegram\Models\Response\ResponseFormat as F;
use XBot\Telegram\Models\Response\TelegramResponse;

class DummyHttpClient implements HttpClientInterface
{
    public function get(string $method, array $parameters = []): TelegramResponse { return TelegramResponse::success([]); }
    public function post(string $method, array $parameters = []): TelegramResponse { return TelegramResponse::success([]); }
    public function upload(string $method, array $parameters = [], array $files = []): TelegramResponse { return TelegramResponse::success([]); }
    public function getToken(): string { return '000:FAKE_TOKEN_DUMMY_______________________________'; }
    public function getBaseUrl(): string { return 'https://api.telegram.org/bot'; }
    public function getConfig(): array { return []; }
    public function setTimeout(int $timeout): static { return $this; }
    public function setRetryAttempts(int $attempts): static { return $this; }
    public function setRetryDelay(int $delay): static { return $this; }
    public function getLastResponse(): ?TelegramResponse { return null; }
    public function getLastError(): ?Throwable { return null; }
}

class ExposedGroup extends BaseMethodGroup
{
    public function __construct() { parent::__construct(new DummyHttpClient(), 'bot', F::ARRAY); }
    public function doPrepare(array $params): array { return $this->prepareParameters($params); }
    public function doExtract(array $params): array { $files = $params; $out = $this->extractFiles($files); return [$files, $out]; }
    public function formatNow(mixed $data): mixed { return $this->formatResult($data); }
}

it('prepares parameters and consumes one-shot format', function () {
    $g = new ExposedGroup();

    $prepared = $g->doPrepare([
        'a' => true,
        'b' => false,
        'c' => ['x' => 1],
        'd' => null,
        'e' => 'ok',
    ]);

    expect($prepared)
        ->toHaveKeys(['a', 'b', 'c', 'e'])
        ->and($prepared['a'])->toBe('true')
        ->and($prepared['b'])->toBe('false')
        ->and(json_decode($prepared['c'], true)['x'])->toBe(1)
        ->and($prepared['e'])->toBe('ok');

    // one-shot format to object
    $g->setOneShotReturnFormat(F::OBJECT);
    $obj = $g->formatNow(['id' => 1]);
    expect($obj)->toBeObject()->and($obj->id)->toBe(1);

    // next call falls back to array
    $arr = $g->formatNow(['id' => 2]);
    expect($arr)->toBeArray()->and($arr['id'])->toBe(2);
});

it('extracts files and replaces with attach uri', function () {
    $g = new ExposedGroup();
    $tmp = tempnam(sys_get_temp_dir(), 'tg');
    file_put_contents($tmp, 'x');

    [$params, $files] = $g->doExtract([
        'chat_id' => 1,
        'photo' => $tmp,
        'nested' => ['doc' => $tmp],
    ]);

    expect($files)->toHaveKeys(['photo', 'nested_doc'])
        ->and($params['photo'])->toBe('attach://photo')
        ->and($params['nested']['doc'])->toBe('attach://nested_doc');
});

