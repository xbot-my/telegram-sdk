<?php

declare(strict_types=1);

use XBot\Telegram\TelegramBot;
use XBot\Telegram\Contracts\HttpClientInterface;
use XBot\Telegram\Models\Response\TelegramResponse;
use XBot\Telegram\Models\Response\ResponseFormat as F;

class FakeHttpClient2 implements HttpClientInterface
{
    public function __construct(private readonly array $responses = []) {}

    public function get(string $method, array $parameters = []): TelegramResponse { return $this->respond($method); }
    public function post(string $method, array $parameters = []): TelegramResponse { return $this->respond($method); }
    public function upload(string $method, array $parameters = [], array $files = []): TelegramResponse { return $this->respond($method); }
    public function getToken(): string { return '000:FAKE_TOKEN_YYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYY'; }
    public function getBaseUrl(): string { return 'https://api.telegram.org/bot'; }
    public function getConfig(): array { return []; }
    public function setTimeout(int $timeout): static { return $this; }
    public function setRetryAttempts(int $attempts): static { return $this; }
    public function setRetryDelay(int $delay): static { return $this; }
    public function getLastResponse(): ?TelegramResponse { return null; }
    public function getLastError(): ?Throwable { return null; }

    private function respond(string $method): TelegramResponse
    {
        $map = $this->responses + [
            'getMe' => ['id' => 1, 'is_bot' => true, 'first_name' => 'T', 'username' => 'tester'],
            'sendMessage' => ['message_id' => 200, 'text' => 'ok', 'chat' => ['id' => 42, 'type' => 'private']],
            'getChat' => ['id' => 42, 'type' => 'private', 'title' => null],
            'copyMessage' => ['message_id' => 201],
        ];
        return TelegramResponse::success($map[$method] ?? null);
    }
}

it('chat.getChat respects format', function () {
    $bot = new TelegramBot('t', new FakeHttpClient2(), []);
    $arr = $bot->chat()->getChat(42);
    expect($arr)->toBeArray()->and($arr['id'])->toBe(42);

    $obj = $bot->as(F::OBJECT)->chat()->getChat(42);
    expect($obj)->toBeObject()->and($obj->id)->toBe(42);
});

it('message.copyMessage returns formatted result', function () {
    $bot = new TelegramBot('t', new FakeHttpClient2(), []);
    $r1 = $bot->copyMessage(42, 42, 200);
    expect($r1)->toBeArray()->and($r1['message_id'])->toBe(201);

    $r2 = $bot->as(F::JSON)->copyMessage(42, 42, 200);
    expect(is_string($r2))->toBeTrue();
    $decoded = json_decode($r2, true);
    expect($decoded['message_id'])->toBe(201);
});

