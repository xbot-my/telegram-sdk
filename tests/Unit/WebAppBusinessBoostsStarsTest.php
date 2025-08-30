<?php

declare(strict_types=1);

use XBot\Telegram\Bot;
use XBot\Telegram\Contracts\Http\Client as ClientContract;
use XBot\Telegram\Http\Response\TelegramResponse;

class FakeHttpClientWBBS implements ClientContract
{
    public function get(string $method, array $parameters = []): TelegramResponse { return $this->post($method, $parameters); }
    public function post(string $method, array $parameters = []): TelegramResponse {
        $map = [
            'answerWebAppQuery' => [ 'ok' => true ],
            'getUserChatBoosts' => [ [ 'boost' => 1 ] ],
            'getChatBoosts' => [ [ 'boost' => 2 ] ],
            'refundStarPayment' => true,
            'getMyStarBalance' => [ 'balance' => 123 ],
            'readBusinessMessage' => true,
            'deleteBusinessMessages' => true,
            'setBusinessAccountName' => true,
            'approveSuggestedPost' => true,
            'declineSuggestedPost' => true,
            'sendPaidMedia' => [ 'message_id' => 77 ],
        ];
        return TelegramResponse::success($map[$method] ?? null);
    }
    public function upload(string $method, array $parameters = [], array $files = []): TelegramResponse { return $this->post($method, $parameters); }
    public function getToken(): string { return '000:WBBS_____'; }
    public function getBaseUrl(): string { return 'https://api.telegram.org/bot'; }
    public function getConfig(): array { return []; }
    public function setTimeout(int $timeout): static { return $this; }
    public function setRetryAttempts(int $attempts): static { return $this; }
    public function setRetryDelay(int $delay): static { return $this; }
    public function getLastResponse(): ?TelegramResponse { return null; }
    public function getLastError(): ?Throwable { return null; }
}

it('covers webapp, business, boosts, and stars', function () {
    $bot = new Bot(new FakeHttpClientWBBS(), ['name' => 't']);
    expect($bot->answerWebAppQuery('qid', ['type' => 'article', 'id' => '1', 'title' => 'T', 'input_message_content' => ['message_text' => 'hi']])->toArray())->toBeArray();
    expect($bot->getUserChatBoosts(1, 2)->toArray())->toBeArray();
    expect($bot->getChatBoosts(1)->toArray())->toBeArray();
    expect($bot->refundStarPayment(1, 'charge123'))->toBeTrue();
    expect($bot->getMyStarBalance()->toArray()['balance'])->toBe(123);
    expect($bot->readBusinessMessage(1, 10))->toBeTrue();
    expect($bot->deleteBusinessMessages(1, [10, 11]))->toBeTrue();
    expect($bot->setBusinessAccountName('My Biz'))->toBeTrue();
    expect($bot->approveSuggestedPost(1, 22))->toBeTrue();
    expect($bot->declineSuggestedPost(1, 22))->toBeTrue();
    expect($bot->sendPaidMedia(1, [['type' => 'photo', 'media' => 'file_id']])->toArray()['message_id'])->toBe(77);
});
