<?php

declare(strict_types=1);

use XBot\Telegram\Utils\ValidationHelper as V;

it('validates bot token, url, and coordinates', function () {
    // 8 digits + 35 allowed characters
    expect(V::validateBotToken('12345678:AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA'))
        ->toBeTrue();
    expect(V::validateBotToken('bad'))
        ->toBeFalse();

    expect(V::validateUrl('https://example.com'))
        ->toBeTrue();
    expect(V::validateUrl('http://example.com', true))
        ->toBeFalse();

    expect(V::validateCoordinates(0.0, 0.0))
        ->toBeTrue();
    expect(V::validateCoordinates(95.0, 0.0))
        ->toBeFalse();
});
