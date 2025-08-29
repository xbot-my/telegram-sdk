<?php

declare(strict_types=1);

use XBot\Telegram\TelegramBot;
use XBot\Telegram\Contracts\HttpClientInterface;
use XBot\Telegram\Models\Response\TelegramResponse;
use XBot\Telegram\Models\Response\ResponseFormat as F;

class FakeHttpClientInvite implements HttpClientInterface
{
    public function get(string $method, array $parameters = []): TelegramResponse { return $this->respond($method); }
    public function post(string $method, array $parameters = []): TelegramResponse { return $this->respond($method); }
    public function upload(string $method, array $parameters = [], array $files = []): TelegramResponse { return $this->respond($method); }
    public function getToken(): string { return '000:FAKE_TOKEN_INVITE_______________________________'; }
    public function getBaseUrl(): string { return 'https://api.telegram.org/bot'; }
    public function getConfig(): array { return []; }
    public function setTimeout(int $timeout): static { return $this; }
    public function setRetryAttempts(int $attempts): static { return $this; }
    public function setRetryDelay(int $delay): static { return $this; }
    public function getLastResponse(): ?TelegramResponse { return null; }
    public function getLastError(): ?Throwable { return null; }

    private function respond(string $method): TelegramResponse
    {
        $map = [
            'createChatInviteLink' => ['invite_link' => 'https://t.me/+abcd'],
            'editChatInviteLink' => ['invite_link' => 'https://t.me/+efgh'],
            'revokeChatInviteLink' => ['invite_link' => ''],
        ];
        return TelegramResponse::success($map[$method] ?? null);
    }
}

it('chat invite link methods return formatted payloads', function () {
    $bot = new TelegramBot('t', new FakeHttpClientInvite(), []);
    $created = $bot->createChatInviteLink(1, []);
    expect($created)->toBeArray()->and($created['invite_link'])->toBe('https://t.me/+abcd');

    $edited = $bot->as(F::OBJECT)->editChatInviteLink(1, 'https://t.me/+abcd');
    expect($edited)->toBeObject()->and($edited->invite_link)->toBe('https://t.me/+efgh');

    $revoked = $bot->revokeChatInviteLink(1, 'https://t.me/+efgh');
    expect($revoked)->toBeArray()->and($revoked['invite_link'])->toBe('');
});

