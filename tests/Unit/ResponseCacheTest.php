<?php

declare(strict_types=1);

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Psr\SimpleCache\CacheInterface;
use Psr\SimpleCache\InvalidArgumentException;
use XBot\Telegram\Http\Middleware\ResponseCache;

describe('ResponseCache', function () {
    beforeEach(function () {
        $this->cache = \Mockery::mock(CacheInterface::class);
        $this->middleware = new ResponseCache($this->cache);
    });

    afterEach(function () {
        \Mockery::close();
    });

    it('tracks cache hits and misses for GET requests', function () {
        $request = new Request('GET', 'https://api.telegram.org/bot/getMe');
        $response = new Response(200, [], 'ok');

        $stored = null;
        $call = 0;

        $this->cache->shouldReceive('get')
            ->twice()
            ->andReturnUsing(function () use (&$call, &$stored) {
                return $call++ === 0 ? null : $stored;
            });

        $this->cache->shouldReceive('set')
            ->once()
            ->andReturnUsing(function ($key, $value) use (&$stored) {
                $stored = $value;
                return true;
            });

        expect($this->middleware->get($request))->toBeNull();
        expect($this->middleware->put($request, $response))->toBeTrue();
        $cached = $this->middleware->get($request);
        expect($cached)->not->toBeNull();
        expect($cached->getStatusCode())->toBe(200);

        $stats = $this->middleware->getStats();
        expect($stats['hits'])->toBe(1)
            ->and($stats['misses'])->toBe(1)
            ->and($stats['writes'])->toBe(1);
    });

    it('respects status code and uri cache rules', function () {
        $request1 = new Request('GET', 'https://api.telegram.org/bot/sendMessage');
        $response1 = new Response(200);

        $request2 = new Request('GET', 'https://api.telegram.org/bot/getMe');
        $response2 = new Response(500);

        $this->cache->shouldReceive('set')->never();

        expect($this->middleware->put($request1, $response1))->toBeFalse();
        expect($this->middleware->put($request2, $response2))->toBeFalse();
    });

    it('compresses cached responses', function () {
        $request = new Request('GET', 'https://api.telegram.org/bot/getMe');
        $body = str_repeat('a', 5000);
        $response = new Response(200, [], $body);

        $stored = null;

        $this->cache->shouldReceive('set')
            ->once()
            ->andReturnUsing(function ($key, $value) use (&$stored) {
                $stored = $value;
                return true;
            });

        $this->middleware->put($request, $response);

        $decoded = base64_decode($stored);
        expect(str_starts_with($decoded, 'compressed:'))->toBeTrue();
    });

    it('enforces maximum cache size', function () {
        $cache = \Mockery::mock(CacheInterface::class);
        $cache->shouldReceive('set')->never();
        $middleware = new ResponseCache($cache, compressCache: false, maxCacheSize: 1024);

        $request = new Request('GET', 'https://api.telegram.org/bot/getMe');
        $body = str_repeat('a', 1000);
        $response = new Response(200, [], $body);

        expect($middleware->put($request, $response))->toBeFalse();
    });

    it('records cache errors', function () {
        $request = new Request('GET', 'https://api.telegram.org/bot/getMe');
        $exception = new class extends \Exception implements InvalidArgumentException {};

        $this->cache->shouldReceive('get')
            ->once()
            ->andThrow($exception);

        expect($this->middleware->get($request))->toBeNull();

        $stats = $this->middleware->getStats();
        expect($stats['errors'])->toBe(1);
    });
});
