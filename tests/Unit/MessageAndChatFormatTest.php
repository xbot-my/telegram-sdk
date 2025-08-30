<?php

declare( strict_types = 1 );

use XBot\Telegram\Bot;
use XBot\Telegram\Contracts\Http\Client as ClientContract;
use XBot\Telegram\Http\Response\TelegramResponse;
// one-shot ResponseFormat removed; use explicit format setters

class FakeHttpClient2 implements ClientContract
{
    public function __construct( private readonly array $responses = [] ) {}
    
    public function get( string $method, array $parameters = [] ): TelegramResponse
    {
        return $this->respond($method);
    }
    
    public function post( string $method, array $parameters = [] ): TelegramResponse
    {
        return $this->respond($method);
    }
    
    public function upload( string $method, array $parameters = [], array $files = [] ): TelegramResponse
    {
        return $this->respond($method);
    }
    
    public function getToken(): string
    {
        return '000:FAKE_TOKEN_YYYYYYYYYYYYYYYYYYYYYYYYYYYYYYYY';
    }
    
    public function getBaseUrl(): string
    {
        return 'https://api.telegram.org/bot';
    }
    
    public function getConfig(): array
    {
        return [];
    }
    
    public function setTimeout( int $timeout ): static
    {
        return $this;
    }
    
    public function setRetryAttempts( int $attempts ): static
    {
        return $this;
    }
    
    public function setRetryDelay( int $delay ): static
    {
        return $this;
    }
    
    public function getLastResponse(): ?TelegramResponse
    {
        return null;
    }
    
    public function getLastError(): ?Throwable
    {
        return null;
    }
    
    private function respond( string $method ): TelegramResponse
    {
        $map = $this->responses + [
                'getMe'       => [ 'id' => 1, 'is_bot' => true, 'first_name' => 'T', 'username' => 'tester' ],
                'sendMessage' => [ 'message_id' => 200, 'text' => 'ok', 'chat' => [ 'id' => 42, 'type' => 'private' ] ],
                'getChat'     => [ 'id' => 42, 'type' => 'private', 'title' => null ],
                'copyMessage' => [ 'message_id' => 201 ],
            ];
        
        return TelegramResponse::success($map[$method] ?? null);
    }
}

it('getChat respects format', function () {
    $bot = new Bot(new FakeHttpClient2(), ['name' => 't']);
    $arr = $bot->getChat(42)->toArray();
    expect($arr)->toBeArray()->and($arr['id'])->toBe(42);
    
    $obj = $bot->getChat(42)->toObject();
    expect($obj)->toBeObject()->and($obj->id)->toBe(42);
});

it('message.copyMessage returns formatted result', function () {
    $bot = new Bot(new FakeHttpClient2(), ['name' => 't']);
    $r1  = $bot->copyMessage(42, 42, 200)->toArray();
    expect($r1)->toBeArray()->and($r1['message_id'])->toBe(201);
    
    $r2 = $bot->copyMessage(42, 42, 200)->toJson();
    expect(is_string($r2))->toBeTrue();
    $decoded = json_decode($r2, true);
    expect($decoded['message_id'])->toBe(201);
});
