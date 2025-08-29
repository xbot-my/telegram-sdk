<?php

declare(strict_types=1);

use XBot\Telegram\Contracts\HttpClientInterface;
use XBot\Telegram\Methods\BaseMethodGroup;
use XBot\Telegram\Models\Response\TelegramResponse;

class ValidatingHttpClient implements HttpClientInterface
{
    public function get(string $method, array $parameters = []): TelegramResponse { return TelegramResponse::success([]); }
    public function post(string $method, array $parameters = []): TelegramResponse { return TelegramResponse::success([]); }
    public function upload(string $method, array $parameters = [], array $files = []): TelegramResponse { return TelegramResponse::success([]); }
    public function getToken(): string { return '000:FAKE_TOKEN_VALIDATE____________________________'; }
    public function getBaseUrl(): string { return 'https://api.telegram.org/bot'; }
    public function getConfig(): array { return []; }
    public function setTimeout(int $timeout): static { return $this; }
    public function setRetryAttempts(int $attempts): static { return $this; }
    public function setRetryDelay(int $delay): static { return $this; }
    public function getLastResponse(): ?TelegramResponse { return null; }
    public function getLastError(): ?Throwable { return null; }
}

class ValidatingGroup extends BaseMethodGroup
{
    public function __construct()
    {
        parent::__construct(new ValidatingHttpClient(), 'bot');
    }

    public function callValidateRequired(array $params, array $required): void { $this->validateRequired($params, $required); }
    public function callValidateText(string $text, int $max = 4096): void { $this->validateTextLength($text, $max); }
    public function callValidateUrl(string $url, bool $https = false): void { $this->validateUrl($url, $https); }
    public function callValidateCoords(float $lat, float $lng): void { $this->validateCoordinates($lat, $lng); }
}

it('validates required fields and limits', function () {
    $g = new ValidatingGroup();
    expect(fn() => $g->callValidateRequired(['chat_id' => 1], ['chat_id']))->not->toThrow(Exception::class);
    expect(fn() => $g->callValidateRequired([], ['chat_id']))->toThrow(InvalidArgumentException::class);

    expect(fn() => $g->callValidateText(str_repeat('a', 5), 5))->not->toThrow(Exception::class);
    expect(fn() => $g->callValidateText(str_repeat('a', 6), 5))->toThrow(InvalidArgumentException::class);
});

it('validates url and coordinates', function () {
    $g = new ValidatingGroup();
    expect(fn() => $g->callValidateUrl('https://example.com', true))->not->toThrow(Exception::class);
    expect(fn() => $g->callValidateUrl('http://example.com', true))->toThrow(InvalidArgumentException::class);
    expect(fn() => $g->callValidateUrl('notaurl', false))->toThrow(InvalidArgumentException::class);

    expect(fn() => $g->callValidateCoords(0.0, 0.0))->not->toThrow(Exception::class);
    expect(fn() => $g->callValidateCoords(91.0, 0.0))->toThrow(InvalidArgumentException::class);
    expect(fn() => $g->callValidateCoords(0.0, 181.0))->toThrow(InvalidArgumentException::class);
});

