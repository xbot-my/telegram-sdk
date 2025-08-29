<?php

declare(strict_types=1);

use XBot\Telegram\TelegramBot;
use XBot\Telegram\Contracts\HttpClientInterface;
use XBot\Telegram\Models\Response\TelegramResponse;

class FakeHttpClientMedia implements HttpClientInterface
{
    public array $lastCall = [];

    public function get(string $method, array $parameters = []): TelegramResponse { return $this->respond($method); }
    public function post(string $method, array $parameters = []): TelegramResponse { $this->lastCall = [$method, $parameters]; return $this->respond($method); }
    public function upload(string $method, array $parameters = [], array $files = []): TelegramResponse { $this->lastCall = [$method, $parameters, $files]; return $this->respond($method); }
    public function getToken(): string { return '000:FAKE_TOKEN_MEDIA___________________________'; }
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
            'sendMessage' => ['message_id' => 1, 'text' => 'HTML', 'chat' => ['id' => 9, 'type' => 'private']],
            'sendPhoto' => ['message_id' => 2, 'photo' => [['file_id' => 'abc']], 'chat' => ['id' => 9, 'type' => 'private']],
        ];
        return TelegramResponse::success($map[$method] ?? null);
    }
}

it('sendMessage supports options and returns array', function () {
    $http = new FakeHttpClientMedia();
    $bot = new TelegramBot('t', $http, []);

    $res = $bot->sendMessage(9, '<b>HTML</b>', ['parse_mode' => 'HTML', 'disable_notification' => true]);
    expect($res)->toBeArray()->and($res['message_id'])->toBe(1);
});

it('sendPhoto switches to upload when given file path', function () {
    $http = new FakeHttpClientMedia();
    $bot = new TelegramBot('t', $http, []);

    $tmp = tempnam(sys_get_temp_dir(), 't-photo-');
    file_put_contents($tmp, 'img');

    $res = $bot->sendPhoto(9, $tmp, ['caption' => 'pic']);
    expect($res)->toBeArray()->and($res['message_id'])->toBe(2);

    // cleanup
    @unlink($tmp);
});

