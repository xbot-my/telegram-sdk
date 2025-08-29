<?php

declare(strict_types=1);

use XBot\Telegram\TelegramBot;
use XBot\Telegram\Contracts\HttpClientInterface;
use XBot\Telegram\Models\Response\TelegramResponse;
use XBot\Telegram\Models\Response\ResponseFormat;

class FakeHttpClient implements HttpClientInterface
{
    public function __construct(private readonly array $responses = []) {}

    public function get(string $method, array $parameters = []): TelegramResponse
    {
        return $this->respond($method);
    }

    public function post(string $method, array $parameters = []): TelegramResponse
    {
        return $this->respond($method);
    }

    public function upload(string $method, array $parameters = [], array $files = []): TelegramResponse
    {
        return $this->respond($method);
    }

    public function getToken(): string { return '000:FAKE_TOKEN_XXXXXXXXXXXXXXXXXXXXXXXXXXXX'; }
    public function getBaseUrl(): string { return 'https://api.telegram.org/bot'; }
    public function getConfig(): array { return []; }
    public function setTimeout(int $timeout): static { return $this; }
    public function setRetryAttempts(int $attempts): static { return $this; }
    public function setRetryDelay(int $delay): static { return $this; }
    public function getLastResponse(): ?TelegramResponse { return null; }
    public function getLastError(): ?Throwable { return null; }

    private function respond(string $method): TelegramResponse
    {
        $map = array_merge([
            'getMe' => ['id' => 1, 'is_bot' => true, 'first_name' => 'Test', 'username' => 'tester'],
            'sendMessage' => ['message_id' => 100, 'text' => 'hello', 'chat' => ['id' => 1, 'type' => 'private']],
            'getChat' => ['id' => 1, 'type' => 'private', 'title' => null],
        ], $this->responses);

        $payload = $map[$method] ?? null;

        return TelegramResponse::success($payload);
    }
}

it('returns array by default for getMe', function () {
    $bot = new TelegramBot('test', new FakeHttpClient(), []);
    $me = $bot->getMe();
    expect($me)->toBeArray()->and($me['username'])->toBe('tester');
});

it('returns object when format is object', function () {
    $bot = new TelegramBot('test', new FakeHttpClient(), []);
    $me = $bot->as(ResponseFormat::OBJECT)->getMe();
    expect($me)->toBeObject()->and($me->username)->toBe('tester');
});

it('returns json when format is json', function () {
    $bot = new TelegramBot('test', new FakeHttpClient(), []);
    $json = $bot->as(ResponseFormat::JSON)->getMe();
    $decoded = json_decode($json, true);
    expect(is_string($json))->toBeTrue()->and($decoded['username'])->toBe('tester');
});

it('returns collection when format is collection', function () {
    if (!class_exists(\Illuminate\Support\Collection::class)) {
        $this->markTestSkipped('Illuminate Collection not available');
    }
    $bot = new TelegramBot('test', new FakeHttpClient(), []);
    $col = $bot->as(ResponseFormat::COLLECTION)->getMe();
    expect($col)->toBeInstanceOf(\Illuminate\Support\Collection::class)
        ->and($col->get('username'))->toBe('tester');
});

it('applies format to other methods like sendMessage and chat.getChat', function () {
    $bot = new TelegramBot('test', new FakeHttpClient(), []);
    $msg = $bot->sendMessage(1, 'hello');
    expect($msg)->toBeArray()->and($msg['message_id'])->toBe(100);

    $chat = $bot->chat()->getChat(1);
    expect($chat)->toBeArray()->and($chat['id'])->toBe(1);
});

