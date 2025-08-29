<?php

declare(strict_types=1);

use XBot\Telegram\TelegramBot;
use XBot\Telegram\Contracts\HttpClientInterface;
use XBot\Telegram\Models\Response\TelegramResponse;

class FakeHttpClientInline implements HttpClientInterface
{
    public function get(string $method, array $parameters = []): TelegramResponse { return $this->respond($method); }
    public function post(string $method, array $parameters = []): TelegramResponse { return $this->respond($method); }
    public function upload(string $method, array $parameters = [], array $files = []): TelegramResponse { return $this->respond($method); }
    public function getToken(): string { return '000:FAKE_TOKEN_INLINE_________________________________'; }
    public function getBaseUrl(): string { return 'https://api.telegram.org/bot'; }
    public function getConfig(): array { return []; }
    public function setTimeout(int $timeout): static { return $this; }
    public function setRetryAttempts(int $attempts): static { return $this; }
    public function setRetryDelay(int $delay): static { return $this; }
    public function getLastResponse(): ?TelegramResponse { return null; }
    public function getLastError(): ?Throwable { return null; }

    private function respond(string $method): TelegramResponse
    {
        $bools = ['answerCallbackQuery', 'answerInlineQuery'];
        return in_array($method, $bools, true) ? TelegramResponse::success(true) : TelegramResponse::success(null);
    }
}

it('answers callback and inline queries', function () {
    $bot = new TelegramBot('t', new FakeHttpClientInline(), []);
    expect($bot->answerCallbackQuery('id123'))->toBeTrue();
    expect($bot->answerInlineQuery('id456', [['type' => 'article','id'=>'1','title'=>'t','input_message_content'=>['message_text'=>'x']]]))->toBeTrue();
});

