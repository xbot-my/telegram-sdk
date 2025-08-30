<?php

declare(strict_types=1);

use XBot\Telegram\Bot;
use XBot\Telegram\Contracts\Http\Client as ClientContract;
use XBot\Telegram\Http\Response\TelegramResponse;

class FakeHttpClientMsgChat implements ClientContract
{
    public array $last = [];
    public function get(string $method, array $parameters = []): TelegramResponse { return $this->post($method, $parameters); }
    public function post(string $method, array $parameters = []): TelegramResponse {
        $this->last = [$method, $parameters];
        $map = [
            'sendSticker' => [ 'message_id' => 1, 'sticker' => [ 'file_id' => 'st' ] ],
            'sendContact' => [ 'message_id' => 2, 'contact' => [ 'phone_number' => $parameters['phone_number'] ?? '' ] ],
            'sendPoll'    => [ 'message_id' => 3, 'poll' => [ 'question' => $parameters['question'] ?? '' ] ],
            'sendChatAction' => true,
            'setChatPhoto' => true,
            'deleteChatPhoto' => true,
            'unpinChatMessage' => true,
        ];
        return TelegramResponse::success($map[$method] ?? null);
    }
    public function upload(string $method, array $parameters = [], array $files = []): TelegramResponse { return $this->post($method, $parameters); }
    public function getToken(): string { return '000:MSGCHAT_____'; }
    public function getBaseUrl(): string { return 'https://api.telegram.org/bot'; }
    public function getConfig(): array { return []; }
    public function setTimeout(int $timeout): static { return $this; }
    public function setRetryAttempts(int $attempts): static { return $this; }
    public function setRetryDelay(int $delay): static { return $this; }
    public function getLastResponse(): ?TelegramResponse { return null; }
    public function getLastError(): ?Throwable { return null; }
}

it('sends sticker, contact, poll and chat action; manages chat photo and unpin', function () {
    $bot = new Bot(new FakeHttpClientMsgChat(), ['name' => 't']);
    expect($bot->sendSticker(1, 'sticker_id')->toArray()['message_id'])->toBe(1);
    expect($bot->sendContact(1, '+123', 'John')->toArray()['message_id'])->toBe(2);
    expect($bot->sendPoll(1, 'Q?', ['A','B'])->toArray()['message_id'])->toBe(3);
    expect($bot->sendChatAction(1, 'typing'))->toBeTrue();
    expect($bot->setChatPhoto(1, 'file.jpg'))->toBeTrue();
    expect($bot->deleteChatPhoto(1))->toBeTrue();
    expect($bot->unpinChatMessage(1, 10))->toBeTrue();
});

