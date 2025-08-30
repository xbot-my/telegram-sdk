<?php

declare( strict_types = 1 );

use XBot\Telegram\Bot;
use XBot\Telegram\Contracts\Http\Client as ClientContract;
use XBot\Telegram\Http\Response\TelegramResponse;
// one-shot ResponseFormat removed; use explicit format setters

class FakeHttpClientInvite implements ClientContract
{
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
        return '000:FAKE_TOKEN_INVITE_______________________________';
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
            'createChatInviteLink' => [ 'invite_link' => 'https://t.me/+abcd' ],
            'editChatInviteLink'   => [ 'invite_link' => 'https://t.me/+efgh' ],
            'revokeChatInviteLink' => [ 'invite_link' => '' ],
        ];
        
        return TelegramResponse::success($map[$method] ?? null);
    }
}

it('chat invite link methods return formatted payloads', function () {
    $bot     = new Bot(new FakeHttpClientInvite(), ['name' => 't']);
    $created = $bot->createChatInviteLink(1, [])->toArray();
    expect($created)->toBeArray()->and($created['invite_link'])->toBe('https://t.me/+abcd');
    
    $edited = $bot->editChatInviteLink(1, 'https://t.me/+abcd')->toObject();
    expect($edited)->toBeObject()->and($edited->invite_link)->toBe('https://t.me/+efgh');
    
    $revoked = $bot->revokeChatInviteLink(1, 'https://t.me/+efgh')->toArray();
    expect($revoked)->toBeArray()->and($revoked['invite_link'])->toBe('');
});
