<?php

declare(strict_types=1);

use XBot\Telegram\TelegramBot;
use XBot\Telegram\Contracts\HttpClientInterface;
use XBot\Telegram\Models\Response\TelegramResponse;

class FakeHttpClientStats implements HttpClientInterface
{
    public function get(string $method, array $parameters = []): TelegramResponse { return $this->respond($method); }
    public function post(string $method, array $parameters = []): TelegramResponse { return $this->respond($method); }
    public function upload(string $method, array $parameters = [], array $files = []): TelegramResponse { return $this->respond($method); }
    public function getToken(): string { return '000:FAKE_TOKEN_STATS______________________________'; }
    public function getBaseUrl(): string { return 'https://api.telegram.org/bot'; }
    public function getConfig(): array { return []; }
    public function setTimeout(int $timeout): static { return $this; }
    public function setRetryAttempts(int $attempts): static { return $this; }
    public function setRetryDelay(int $delay): static { return $this; }
    public function getLastResponse(): ?TelegramResponse { return null; }
    public function getLastError(): ?Throwable { return null; }
    public function getStats(): array { return []; }

    private function respond(string $method): TelegramResponse
    {
        if ($method === 'getMe') {
            return TelegramResponse::success(['id' => 1, 'is_bot' => true, 'first_name' => 'T', 'username' => 't']);
        }
        return TelegramResponse::success(true);
    }
}

it('reports health and stats', function () {
    $bot = new TelegramBot('t', new FakeHttpClientStats(), []);
    expect($bot->healthCheck())->toBeTrue();

    // trigger a couple of calls to populate stats
    $bot->getMe();
    $bot->call('getMe');

    $stats = $bot->getStats();
    expect($stats)
        ->toHaveKeys(['name', 'token', 'created_at', 'uptime', 'uptime_formatted', 'total_calls', 'successful_calls'])
        ->and($stats['total_calls'])->toBeGreaterThan(0);
});
