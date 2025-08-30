<?php

declare( strict_types = 1 );

namespace XBot\Telegram\Tests\Support;

use XBot\Telegram\Contracts\Http\Client as ClientContract;
use XBot\Telegram\Http\Response\TelegramResponse;

class FakeHttpClient implements ClientContract
{
    private string            $token;
    private string            $baseUrl;
    private array             $config;
    private ?TelegramResponse $last      = null;
    private ?\Throwable       $lastError = null;
    
    /** @var callable(string $method, array $params, array $files): array */
    private $handler;
    
    public function __construct( string $token = '123456789:AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA', array $config = [], ?callable $handler = null )
    {
        $this->token   = $token;
        $this->baseUrl = $config['base_url'] ?? 'https://api.telegram.org/bot';
        $this->config  = $config + [ 'timeout' => 1 ];
        $this->handler = $handler ?? function ( string $method, array $params, array $files ): array {
            // Default echo behavior for simple tests
            return [
                'ok'     => true,
                'result' => [ 'method' => $method, 'params' => $params, 'files' => array_keys($files) ],
            ];
        };
    }
    
    public function get( string $method, array $parameters = [] ): TelegramResponse
    {
        return $this->respond($method, $parameters);
    }
    
    public function post( string $method, array $parameters = [] ): TelegramResponse
    {
        return $this->respond($method, $parameters);
    }
    
    public function upload( string $method, array $parameters = [], array $files = [] ): TelegramResponse
    {
        return $this->respond($method, $parameters, $files);
    }
    
    private function respond( string $method, array $parameters = [], array $files = [] ): TelegramResponse
    {
        try {
            $data = ( $this->handler )($method, $parameters, $files);
            
            return $this->last = new TelegramResponse($data);
        }
        catch (\Throwable $e) {
            $this->lastError = $e;
            throw $e;
        }
    }
    
    public function getToken(): string
    {
        return $this->token;
    }
    
    public function getBaseUrl(): string
    {
        return $this->baseUrl;
    }
    
    public function getConfig(): array
    {
        return $this->config;
    }
    
    public function setTimeout( int $timeout ): static
    {
        $this->config['timeout'] = $timeout;
        
        return $this;
    }
    
    public function setRetryAttempts( int $attempts ): static
    {
        $this->config['retry_attempts'] = $attempts;
        
        return $this;
    }
    
    public function setRetryDelay( int $delay ): static
    {
        $this->config['retry_delay'] = $delay;
        
        return $this;
    }
    
    public function getLastResponse(): ?TelegramResponse
    {
        return $this->last;
    }
    
    public function getLastError(): ?\Throwable
    {
        return $this->lastError;
    }
}
