<?php

declare(strict_types=1);

use XBot\Telegram\Inline\InlineKeyboard;
use XBot\Telegram\Inline\InlineButton;
use XBot\Telegram\Keyboard\ReplyKeyboard;
use XBot\Telegram\Keyboard\ReplyButton;
use XBot\Telegram\Keyboard\ForceReply;
use XBot\Telegram\Keyboard\ReplyKeyboardRemove;
use XBot\Telegram\Bot;
use XBot\Telegram\Contracts\Http\Client as ClientContract;
use XBot\Telegram\Http\Response\TelegramResponse;

class FakeHttpClientKB implements ClientContract
{
    public array $last = [];
    public function get(string $method, array $parameters = []): TelegramResponse { return $this->post($method, $parameters); }
    public function post(string $method, array $parameters = []): TelegramResponse {
        $this->last = [$method, $parameters];
        $map = [ 'sendMessage' => [ 'message_id' => 77, 'text' => $parameters['text'] ?? '' ] ];
        return TelegramResponse::success($map[$method] ?? null);
    }
    public function upload(string $method, array $parameters = [], array $files = []): TelegramResponse { return $this->post($method, $parameters); }
    public function getToken(): string { return '000:K______'; }
    public function getBaseUrl(): string { return 'https://api.telegram.org/bot'; }
    public function getConfig(): array { return []; }
    public function setTimeout(int $timeout): static { return $this; }
    public function setRetryAttempts(int $attempts): static { return $this; }
    public function setRetryDelay(int $delay): static { return $this; }
    public function getLastResponse(): ?TelegramResponse { return null; }
    public function getLastError(): ?Throwable { return null; }
}

it('builds inline and reply keyboards and serializes in options', function () {
    $inline = InlineKeyboard::make()
        ->row()->button(InlineButton::callback('A', 'a'))
        ->row()->button(InlineButton::url('B', 'https://example.com'))
        ->toArray();

    $reply = ReplyKeyboard::make()
        ->resize()->once()->placeholder('Type...')->selective()
        ->row()->button(ReplyButton::text('Hello'))
        ->row()->button(ReplyButton::location('Share Location'))
        ->toArray();

    expect($inline['inline_keyboard'])->toHaveCount(2);
    expect($reply['keyboard'])->toHaveCount(2);

    $http = new FakeHttpClientKB();
    $bot = new Bot($http, ['name' => 't']);

    $bot->sendMessage(1, 'k', [ 'reply_markup' => $inline ]);
    [$m, $params] = $http->last;
    expect($m)->toBe('sendMessage');
    expect($params['reply_markup'])->toBeJson();
});

it('provides force reply and remove helpers', function () {
    $force = ForceReply::make(true, 'Reply pls');
    $remove = ReplyKeyboardRemove::make(true);
    expect($force['force_reply'])->toBeTrue();
    expect($remove['remove_keyboard'])->toBeTrue();
});
