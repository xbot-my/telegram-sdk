<?php

declare(strict_types=1);

use XBot\Telegram\Http\HttpClientConfig;

it('normalizes values and builds urls', function () {
    $token = '12345678:ABCDEFGHIJKLMNOPQRSTUVWXYZabcdef0123456789';
    $cfg = new HttpClientConfig(
        token: $token,
        baseUrl: 'https://api.telegram.org/bot/',
        timeout: 0, // will be max(1, 0) = 1
        retryAttempts: -1, // -> 0
        retryDelay: -5, // -> 0
        connectTimeout: 0, // -> 1
        readTimeout: 0 // -> 1
    );

    expect($cfg->getBaseUrl())->toBe('https://api.telegram.org/bot')
        ->and($cfg->getTimeout())->toBe(1)
        ->and($cfg->getRetryAttempts())->toBe(0)
        ->and($cfg->getRetryDelay())->toBe(0)
        ->and($cfg->getConnectTimeout())->toBe(1)
        ->and($cfg->getReadTimeout())->toBe(1)
        ->and($cfg->getApiUrl())->toBe('https://api.telegram.org/bot' . $token . '/')
        ->and($cfg->getFileApiUrl())->toBe('https://api.telegram.org/file/bot' . $token . '/');
});

it('creates from array, validates, and stringifies safely', function () {
    $cfg = HttpClientConfig::fromArray([
        'token' => '12345678:ABCDEFGHIJKLMNOPQRSTUVWXYZabcdef0123456789',
        'base_url' => 'https://api.telegram.org/bot',
        'timeout' => 30,
        'retry_attempts' => 3,
        'retry_delay' => 1000,
        'verify_ssl' => true,
    ], 'botX');

    $cfg->validate();
    $asArray = $cfg->toArray();
    $json = (string) $cfg;
    expect($asArray)->toHaveKeys(['api_url', 'file_api_url', 'token_validation'])
        ->and($json)->toContain('"token"')
        ->and($json)->toContain('...'); // token field masked

    $changed = $cfg->with(['timeout' => 5]);
    expect($changed->getTimeout())->toBe(5)
        ->and($changed)->not->toBe($cfg);
});

it('throws on invalid config', function () {
    // missing token
    expect(fn() => HttpClientConfig::fromArray([]))->toThrow(InvalidArgumentException::class);

    // bad base url
    $cfg = new HttpClientConfig('12345678:ABCDEFGHIJKLMNOPQRSTUVWXYZabcdef0123456789', 'not-a-url');
    expect(fn() => $cfg->validate())->toThrow(InvalidArgumentException::class);
});
