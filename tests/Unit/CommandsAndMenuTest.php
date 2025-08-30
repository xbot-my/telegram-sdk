<?php

declare(strict_types=1);

use XBot\Telegram\Bot;
use XBot\Telegram\Contracts\Http\Client as ClientContract;
use XBot\Telegram\Http\Response\TelegramResponse;

class FakeHttpClientCMD implements ClientContract
{
    public function get(string $method, array $parameters = []): TelegramResponse { return $this->post($method, $parameters); }
    public function post(string $method, array $parameters = []): TelegramResponse {
        $map = [
            'setMyCommands' => true,
            'getMyCommands' => [ [ 'command' => 'start', 'description' => 'Start' ] ],
            'deleteMyCommands' => true,
            'setChatMenuButton' => true,
            'getChatMenuButton' => [ 'type' => 'default' ],
            'setMyName' => true,
            'getMyName' => [ 'name' => 'MyBot' ],
            'setMyDescription' => true,
            'getMyDescription' => [ 'description' => 'Desc' ],
            'setMyShortDescription' => true,
            'getMyShortDescription' => [ 'short_description' => 'Short' ],
            'setMyDefaultAdministratorRights' => true,
            'getMyDefaultAdministratorRights' => [ 'can_manage_chat' => true ],
        ];
        return TelegramResponse::success($map[$method] ?? null);
    }
    public function upload(string $method, array $parameters = [], array $files = []): TelegramResponse { return $this->post($method, $parameters); }
    public function getToken(): string { return '000:CMD_____'; }
    public function getBaseUrl(): string { return 'https://api.telegram.org/bot'; }
    public function getConfig(): array { return []; }
    public function setTimeout(int $timeout): static { return $this; }
    public function setRetryAttempts(int $attempts): static { return $this; }
    public function setRetryDelay(int $delay): static { return $this; }
    public function getLastResponse(): ?TelegramResponse { return null; }
    public function getLastError(): ?Throwable { return null; }
}

it('manages bot commands and menu button', function () {
    $bot = new Bot(new FakeHttpClientCMD(), ['name' => 't']);
    expect($bot->setMyCommands([ ['command' => 'start', 'description' => 'Start'] ], ['scope' => ['type' => 'all_private_chats']]))->toBeTrue();
    expect($bot->getMyCommands()->toArray())->toBeArray();
    expect($bot->deleteMyCommands())->toBeTrue();

    expect($bot->setChatMenuButton(['chat_id' => 1, 'menu_button' => ['type' => 'default']]))->toBeTrue();
    expect($bot->getChatMenuButton()->toArray()['type'])->toBe('default');
});

it('manages bot name and descriptions', function () {
    $bot = new Bot(new FakeHttpClientCMD(), ['name' => 't']);
    expect($bot->setMyName('MyBot'))->toBeTrue();
    expect($bot->getMyName()->toArray()['name'])->toBe('MyBot');
    expect($bot->setMyDescription('Desc'))->toBeTrue();
    expect($bot->getMyDescription()->toArray()['description'])->toBe('Desc');
    expect($bot->setMyShortDescription('Short'))->toBeTrue();
    expect($bot->getMyShortDescription()->toArray()['short_description'])->toBe('Short');
});

it('handles default admin rights', function () {
    $bot = new Bot(new FakeHttpClientCMD(), ['name' => 't']);
    expect($bot->setMyDefaultAdministratorRights(['can_manage_chat' => true], false))->toBeTrue();
    expect($bot->getMyDefaultAdministratorRights(false)->toArray()['can_manage_chat'])->toBeTrue();
});
