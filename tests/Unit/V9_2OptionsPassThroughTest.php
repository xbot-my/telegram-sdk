<?php

declare(strict_types=1);

use XBot\Telegram\Bot;
use XBot\Telegram\Contracts\Http\Client as ClientContract;
use XBot\Telegram\Http\Response\TelegramResponse;

class FakeHttpClientV92 implements ClientContract
{
    public array $last = [];
    public function get(string $method, array $parameters = []): TelegramResponse { return $this->post($method, $parameters); }
    public function post(string $method, array $parameters = []): TelegramResponse {
        $this->last = [$method, $parameters];
        $map = [ 'sendMessage' => [ 'message_id' => 1 ] ];
        return TelegramResponse::success($map[$method] ?? null);
    }
    public function upload(string $method, array $parameters = [], array $files = []): TelegramResponse { return $this->post($method, $parameters); }
    public function getToken(): string { return '000:V92_____'; }
    public function getBaseUrl(): string { return 'https://api.telegram.org/bot'; }
    public function getConfig(): array { return []; }
    public function setTimeout(int $timeout): static { return $this; }
    public function setRetryAttempts(int $attempts): static { return $this; }
    public function setRetryDelay(int $delay): static { return $this; }
    public function getLastResponse(): ?TelegramResponse { return null; }
    public function getLastError(): ?Throwable { return null; }
}

it('passes v9.2 options as json fields', function () {
    $http = new FakeHttpClientV92();
    $bot = new Bot($http, ['name' => 't']);

    $bot->sendMessage(1, 'x', [
        'direct_messages_topic_id' => 1234,
        'suggested_post_parameters' => [ 'price' => 100 ],
        'reply_parameters' => [ 'checklist_task_id' => 9 ],
    ]);

    [$m, $params] = $http->last;
    expect($m)->toBe('sendMessage');
    expect($params['direct_messages_topic_id'])->toBe(1234);
    expect($params['suggested_post_parameters'])->toBeJson();
    expect($params['reply_parameters'])->toBeJson();
});

