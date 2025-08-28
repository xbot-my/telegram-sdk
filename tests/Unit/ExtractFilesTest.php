<?php

declare(strict_types=1);

use XBot\Telegram\Contracts\HttpClientInterface;
use XBot\Telegram\Methods\BaseMethodGroup;
use XBot\Telegram\Models\Response\TelegramResponse;

class DummyHttpClient implements HttpClientInterface
{
    public function get(string $method, array $parameters = []): TelegramResponse
    {
        return new TelegramResponse(['ok' => true, 'result' => null]);
    }

    public function post(string $method, array $parameters = []): TelegramResponse
    {
        return new TelegramResponse(['ok' => true, 'result' => null]);
    }

    public function upload(string $method, array $parameters = [], array $files = []): TelegramResponse
    {
        return new TelegramResponse(['ok' => true, 'result' => null]);
    }

    public function getToken(): string
    {
        return '';
    }

    public function getBaseUrl(): string
    {
        return '';
    }

    public function getConfig(): array
    {
        return [];
    }

    public function setTimeout(int $timeout): static
    {
        return $this;
    }

    public function setRetryAttempts(int $attempts): static
    {
        return $this;
    }

    public function setRetryDelay(int $delay): static
    {
        return $this;
    }

    public function getLastResponse(): ?TelegramResponse
    {
        return null;
    }

    public function getLastError(): ?\Throwable
    {
        return null;
    }
}

class TestMethodGroup extends BaseMethodGroup
{
    public function extract(array &$parameters): array
    {
        return $this->extractFiles($parameters);
    }
}

it('extracts top-level files', function () {
    $temp = tempnam(sys_get_temp_dir(), 'tg');
    file_put_contents($temp, 'a');

    $group = new TestMethodGroup(new DummyHttpClient(), 'bot');

    $params = ['photo' => $temp, 'chat_id' => 1];
    $files = $group->extract($params);

    expect($files)->toHaveKey('photo', $temp);
    expect($params['photo'])->toBe('attach://photo');
});

it('extracts nested media files', function () {
    $temp = tempnam(sys_get_temp_dir(), 'tg');
    file_put_contents($temp, 'a');

    $group = new TestMethodGroup(new DummyHttpClient(), 'bot');

    $params = ['media' => [['type' => 'photo', 'media' => $temp]]];
    $files = $group->extract($params);

    expect($files)->toHaveKey('media_0_media', $temp);
    expect($params['media'][0]['media'])->toBe('attach://media_0_media');
});
