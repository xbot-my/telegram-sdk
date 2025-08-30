<?php

declare(strict_types=1);

use XBot\Telegram\Bot;
use XBot\Telegram\Contracts\Http\Client as ClientContract;
use XBot\Telegram\Http\Response\TelegramResponse;

class FakeHttpClientUpdates implements ClientContract
{
    public function get(string $method, array $parameters = []): TelegramResponse { return $this->post($method, $parameters); }
    public function post(string $method, array $parameters = []): TelegramResponse {
        $map = [
            'getUpdates' => [ [ 'update_id' => 1, 'message' => [ 'text' => 'hi' ] ] ],
            'logOut' => true,
            'close' => true,
        ];
        return TelegramResponse::success($map[$method] ?? null);
    }
    public function upload(string $method, array $parameters = [], array $files = []): TelegramResponse { return $this->post($method, $parameters); }
    public function getToken(): string { return '000:UPDATES_____'; }
    public function getBaseUrl(): string { return 'https://api.telegram.org/bot'; }
    public function getConfig(): array { return []; }
    public function setTimeout(int $timeout): static { return $this; }
    public function setRetryAttempts(int $attempts): static { return $this; }
    public function setRetryDelay(int $delay): static { return $this; }
    public function getLastResponse(): ?TelegramResponse { return null; }
    public function getLastError(): ?Throwable { return null; }
}

it('fetches updates and handles session actions', function () {
    $bot = new Bot(new FakeHttpClientUpdates(), ['name' => 't']);
    $updates = $bot->getUpdates(['offset' => 0])->toArray();
    expect($updates)->toBeArray()->and($updates[0]['update_id'])->toBe(1);
    expect($bot->logOut())->toBeTrue();
    expect($bot->close())->toBeTrue();
});

