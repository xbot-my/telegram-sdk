<?php

declare(strict_types=1);

use XBot\Telegram\Bot;
use XBot\Telegram\Contracts\Http\Client as ClientContract;
use XBot\Telegram\Http\Response\TelegramResponse;

class FakeHttpClientForumPay implements ClientContract
{
    public function get(string $method, array $parameters = []): TelegramResponse { return $this->post($method, $parameters); }
    public function post(string $method, array $parameters = []): TelegramResponse {
        $map = [
            // Forum
            'createForumTopic' => [ 'name' => $parameters['name'] ?? 'topic', 'message_thread_id' => 111 ],
            'editForumTopic' => true,
            'closeForumTopic' => true,
            'reopenForumTopic' => true,
            'deleteForumTopic' => true,
            'unpinAllForumTopicMessages' => true,
            'editGeneralForumTopic' => true,
            'closeGeneralForumTopic' => true,
            'reopenGeneralForumTopic' => true,
            'hideGeneralForumTopic' => true,
            'unhideGeneralForumTopic' => true,
            'unpinAllGeneralForumTopicMessages' => true,
            // Payments
            'sendInvoice' => [ 'message_id' => 99, 'invoice' => [ 'title' => $parameters['title'] ?? 't' ] ],
            'createInvoiceLink' => 'https://t.me/invoice/abc',
            'answerShippingQuery' => true,
            'answerPreCheckoutQuery' => true,
        ];
        return TelegramResponse::success($map[$method] ?? null);
    }
    public function upload(string $method, array $parameters = [], array $files = []): TelegramResponse { return $this->post($method, $parameters); }
    public function getToken(): string { return '000:FORUMPAY_____'; }
    public function getBaseUrl(): string { return 'https://api.telegram.org/bot'; }
    public function getConfig(): array { return []; }
    public function setTimeout(int $timeout): static { return $this; }
    public function setRetryAttempts(int $attempts): static { return $this; }
    public function setRetryDelay(int $delay): static { return $this; }
    public function getLastResponse(): ?TelegramResponse { return null; }
    public function getLastError(): ?Throwable { return null; }
}

it('covers forum topic endpoints', function () {
    $bot = new Bot(new FakeHttpClientForumPay(), ['name' => 't']);
    expect($bot->createForumTopic(1, 'Topic')->toArray()['name'])->toBe('Topic');
    expect($bot->editForumTopic(1, 111, ['name' => 'New']))->toBeTrue();
    expect($bot->closeGeneralForumTopic(1))->toBeTrue();
    expect($bot->reopenGeneralForumTopic(1))->toBeTrue();
});

it('covers payments endpoints', function () {
    $bot = new Bot(new FakeHttpClientForumPay(), ['name' => 't']);
    $prices = [ [ 'label' => 'Item', 'amount' => 100 ] ];
    expect($bot->sendInvoice(1, 'T', 'D', 'p', 'prov', 'USD', $prices)->toArray()['message_id'])->toBe(99);
    expect($bot->createInvoiceLink('T', 'D', 'p', 'prov', 'USD', $prices))->toBeString();
    expect($bot->answerShippingQuery('qid', true))->toBeTrue();
    expect($bot->answerPreCheckoutQuery('pcid', true))->toBeTrue();
});

