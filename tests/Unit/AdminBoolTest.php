<?php

declare(strict_types=1);

use XBot\Telegram\TelegramBot;
use XBot\Telegram\Contracts\HttpClientInterface;
use XBot\Telegram\Models\Response\TelegramResponse;

class FakeHttpClientAdmin implements HttpClientInterface
{
    public function get(string $method, array $parameters = []): TelegramResponse { return $this->respond($method); }
    public function post(string $method, array $parameters = []): TelegramResponse { return $this->respond($method); }
    public function upload(string $method, array $parameters = [], array $files = []): TelegramResponse { return $this->respond($method); }
    public function getToken(): string { return '000:FAKE_TOKEN_ADMIN________________________________'; }
    public function getBaseUrl(): string { return 'https://api.telegram.org/bot'; }
    public function getConfig(): array { return []; }
    public function setTimeout(int $timeout): static { return $this; }
    public function setRetryAttempts(int $attempts): static { return $this; }
    public function setRetryDelay(int $delay): static { return $this; }
    public function getLastResponse(): ?TelegramResponse { return null; }
    public function getLastError(): ?Throwable { return null; }

    private function respond(string $method): TelegramResponse
    {
        $bools = [
            'banChatMember', 'unbanChatMember', 'restrictChatMember', 'promoteChatMember',
            'setChatAdministratorCustomTitle', 'banChatSenderChat', 'unbanChatSenderChat',
        ];
        if (in_array($method, $bools, true)) {
            return TelegramResponse::success(true);
        }
        return TelegramResponse::success(null);
    }
}

it('admin core operations return booleans', function () {
    $bot = new TelegramBot('t', new FakeHttpClientAdmin(), []);
    expect($bot->banChatMember(1, 2))->toBeTrue();
    expect($bot->unbanChatMember(1, 2))->toBeTrue();
    expect($bot->restrictChatMember(1, 2, ['can_send_messages' => false]))->toBeTrue();
    expect($bot->promoteChatMember(1, 2, ['can_manage_chat' => true]))->toBeTrue();
});

