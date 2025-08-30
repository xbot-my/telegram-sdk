<?php

declare(strict_types=1);

use XBot\Telegram\Bot;
use XBot\Telegram\Http\Response\TelegramResponse;
use XBot\Telegram\Contracts\Http\Client as ClientContract;

class DummyClientSnake implements ClientContract {
    public function get(string $method, array $parameters = []): TelegramResponse { return $this->post($method, $parameters); }
    public function post(string $method, array $parameters = []): TelegramResponse {
        $map = [ 'getWebhookInfo' => [ 'url' => 'https://example.com/hook' ] ];
        return TelegramResponse::success($map[$method] ?? null);
    }
    public function upload(string $method, array $parameters = [], array $files = []): TelegramResponse { return $this->post($method, $parameters); }
    public function getToken(): string { return '111111:AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA'; }
    public function getBaseUrl(): string { return 'https://api.telegram.org/bot'; }
    public function getConfig(): array { return []; }
    public function setTimeout(int $timeout): static { return $this; }
    public function setRetryAttempts(int $attempts): static { return $this; }
    public function setRetryDelay(int $delay): static { return $this; }
    public function getLastResponse(): ?TelegramResponse { return null; }
    public function getLastError(): ?Throwable { return null; }
}

it('supports snake_case method aliases', function () {
    $bot = new Bot(new DummyClientSnake(), ['name' => 't']);
    expect($bot->get_webhook_info()->toArray()['url'])
        ->toBe('https://example.com/hook');
});
