<?php

declare(strict_types=1);

use XBot\Telegram\TelegramBot;
use XBot\Telegram\Contracts\HttpClientInterface;
use XBot\Telegram\Models\Response\TelegramResponse;
use XBot\Telegram\Exceptions\ApiException;

class FakeHttpClientError implements HttpClientInterface
{
    public function get(string $method, array $parameters = []): TelegramResponse { return $this->respond($method); }
    public function post(string $method, array $parameters = []): TelegramResponse { return $this->respond($method); }
    public function upload(string $method, array $parameters = [], array $files = []): TelegramResponse { return $this->respond($method); }
    public function getToken(): string { return '000:FAKE_TOKEN_ERR___________________________________'; }
    public function getBaseUrl(): string { return 'https://api.telegram.org/bot'; }
    public function getConfig(): array { return []; }
    public function setTimeout(int $timeout): static { return $this; }
    public function setRetryAttempts(int $attempts): static { return $this; }
    public function setRetryDelay(int $delay): static { return $this; }
    public function getLastResponse(): ?TelegramResponse { return null; }
    public function getLastError(): ?Throwable { return null; }

    private function respond(string $method): TelegramResponse
    {
        if ($method === 'getMe') {
            return new TelegramResponse([
                'ok' => false,
                'error_code' => 400,
                'description' => 'Bad Request: getMe failed',
            ]);
        }
        return TelegramResponse::success(null);
    }
}

it('throws ApiException on error responses', function () {
    $bot = new TelegramBot('t', new FakeHttpClientError(), []);
    $this->expectException(ApiException::class);
    $bot->getMe();
});

