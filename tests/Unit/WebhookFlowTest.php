<?php

declare( strict_types = 1 );

use XBot\Telegram\Bot;
use XBot\Telegram\Contracts\Http\Client as ClientContract;
use XBot\Telegram\Http\Response\TelegramResponse;
// one-shot ResponseFormat removed; use explicit format setters

class FakeHttpClientWebhook implements ClientContract
{
    public array $calls = [];
    
    public function get( string $method, array $parameters = [] ): TelegramResponse
    {
        return $this->respond($method);
    }
    
    public function post( string $method, array $parameters = [] ): TelegramResponse
    {
        $this->calls[] = [ $method, $parameters ];
        
        return $this->respond($method);
    }
    
    public function upload( string $method, array $parameters = [], array $files = [] ): TelegramResponse
    {
        $this->calls[] = [ $method, $parameters, $files ];
        
        return $this->respond($method);
    }
    
    public function getToken(): string
    {
        return '000:FAKE_TOKEN_ZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZ';
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
        $map = [
            'setWebhook'     => true,
            'getWebhookInfo' => [
                'url'                    => 'https://example.com/telegram/webhook',
                'has_custom_certificate' => false,
                'pending_update_count'   => 0,
            ],
            'deleteWebhook'  => true,
        ];
        
        return TelegramResponse::success($map[$method] ?? null);
    }
}

it('sets and gets webhook info with formats', function () {
    $http = new FakeHttpClientWebhook();
    $bot  = new Bot($http, ['name' => 't']);
    
    expect($bot->setWebhook('https://example.com/telegram/webhook'))->toBeTrue();
    
    $info = $bot->getWebhookInfo()->toArray();
    expect($info)->toBeArray()->and($info['url'])->toBe('https://example.com/telegram/webhook');
    
    $infoObj = $bot->getWebhookInfo()->toObject();
    expect($infoObj)->toBeObject()->and($infoObj->url)->toBe('https://example.com/telegram/webhook');
    
    expect($bot->deleteWebhook(true))->toBeTrue();
});
