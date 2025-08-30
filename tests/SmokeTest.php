<?php

declare(strict_types=1);

use XBot\Telegram\Bot;
use XBot\Telegram\Http\Response\TelegramResponse;
use XBot\Telegram\Contracts\Http\Client as ClientContract;

class DummyClient implements ClientContract {
    public function get(string $method, array $parameters = []): TelegramResponse { return $this->post($method, $parameters); }
    public function post(string $method, array $parameters = []): TelegramResponse {
        $map = [
            'getMe' => [ 'id' => 1, 'is_bot' => true, 'first_name' => 'T', 'username' => 'tester' ],
            'getChat' => [ 'id' => 42, 'type' => 'private' ],
            'getFile' => [ 'file_id' => 'abc', 'file_path' => 'photos/p.jpg' ],
            'sendMessage' => [ 'message_id' => 10, 'text' => $parameters['text'] ?? 'x' ],
            'sendPhoto' => [ 'message_id' => 11, 'photo' => [[ 'file_id' => 'pid' ]] ],
            'answerCallbackQuery' => true,
            'answerInlineQuery' => true,
            'setWebhook' => true,
            'deleteWebhook' => true,
            'getWebhookInfo' => [ 'url' => 'https://example.com/hook' ],
        ];
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

it('smoke runs core endpoints', function () {
    $bot = new Bot(new DummyClient(), ['name' => 't']);
    expect($bot->getMe()->toArray()['username'])->toBe('tester');
    expect($bot->getChat(42)->toObject()->id)->toBe(42);
    expect(json_decode($bot->sendMessage(1, 'hi')->toJson(), true)['message_id'])->toBe(10);
    expect($bot->sendPhoto(1, 'file.jpg')->toArray()['message_id'])->toBe(11);
    expect($bot->getWebhookInfo()->toArray()['url'])->toBe('https://example.com/hook');
    expect($bot->getFileUrl('abc'))->toBe('https://api.telegram.org/file/bot111111:AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA/photos/p.jpg');
});
