<?php

declare(strict_types=1);

use XBot\Telegram\TelegramBot;
use XBot\Telegram\Contracts\HttpClientInterface;
use XBot\Telegram\Models\Response\TelegramResponse;

class FakeHttpClientFile implements HttpClientInterface
{
    public function get(string $method, array $parameters = []): TelegramResponse { return $this->respond($method); }
    public function post(string $method, array $parameters = []): TelegramResponse { return $this->respond($method); }
    public function upload(string $method, array $parameters = [], array $files = []): TelegramResponse { return $this->respond($method); }
    public function getToken(): string { return '111111:AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA'; }
    public function getBaseUrl(): string { return 'https://api.telegram.org/bot'; }
    public function getConfig(): array { return []; }
    public function setTimeout(int $timeout): static { return $this; }
    public function setRetryAttempts(int $attempts): static { return $this; }
    public function setRetryDelay(int $delay): static { return $this; }
    public function getLastResponse(): ?TelegramResponse { return null; }
    public function getLastError(): ?Throwable { return null; }

    private function respond(string $method): TelegramResponse
    {
        if ($method === 'getFile') {
            return TelegramResponse::success(['file_id' => 'abc', 'file_size' => 10, 'file_path' => 'photos/file.jpg']);
        }
        return TelegramResponse::success(null);
    }
}

it('builds file url from getFile result', function () {
    $bot = new TelegramBot('t', new FakeHttpClientFile(), []);
    $url = $bot->getFileUrl('abc');
    expect($url)->toBe('https://api.telegram.org/file/bot111111:AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA/photos/file.jpg');
});

