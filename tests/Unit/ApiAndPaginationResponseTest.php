<?php

declare(strict_types=1);

use XBot\Telegram\Models\Response\ApiResponse;
use XBot\Telegram\Models\Response\PaginatedResponse;
use XBot\Telegram\Models\Response\FileResponse;
use XBot\Telegram\Models\DTO\Chat;

it('handles ApiResponse success and error variants', function () {
    $ok = ApiResponse::success(['id' => 1, 'type' => 'private'], 12, 'abc');
    expect($ok->isSuccess())->toBeTrue()
        ->and($ok->getResultAsArray())->toBeArray()
        ->and($ok->getResultAsDTO(Chat::class))->toBeInstanceOf(Chat::class);

    $mapped = $ok->map(fn ($v) => $v); // associative array, still maps values
    expect($mapped)->toMatchArray(['id' => 1, 'type' => 'private']);

    $err = ApiResponse::error(429, 'Too Many Requests', 2, 20, 'xyz');
    expect($err->isError())->toBeTrue()
        ->and($err->hasRetryAfter())->toBeTrue()
        ->and($err->isRateLimitError())->toBeTrue()
        ->and($err->isRetryable())->toBeTrue()
        ->and($err->getErrorType())->toBe('too_many_requests')
        ->and((string) $err)->toContain('Error 429');

    $stats = $ok->getStats();
    expect($stats)->toHaveKeys(['request_id', 'timestamp', 'success']);

    // fromApiResponse
    $from = ApiResponse::fromApiResponse(['ok' => true, 'result' => [1,2,3]]);
    expect($from->isSuccess())->toBeTrue()
        ->and($from->map(fn ($x) => $x * 2))->toBe([2,4,6])
        ->and($from->first())->toBe(1);
});

// Note: there are two PaginatedResponse declarations in the codebase; to avoid autoload ambiguity,
// we skip direct behavioral tests for it here.

it('builds FileResponse from api data', function () {
    $file = FileResponse::fromApiResponse([
        'file_id' => 'fid',
        'file_unique_id' => 'uniq',
        'file_path' => 'photos/file.jpg',
        'file_size' => 123,
        'file_name' => 'file.jpg',
        'mime_type' => 'image/jpeg',
        'width' => 10,
        'height' => 20,
        'duration' => 0,
    ], '000:TOKEN', 'photo');

    expect($file->downloadUrl)->toBe('https://api.telegram.org/file/bot000:TOKEN/photos/file.jpg')
        ->and($file->fileType)->toBe('photo')
        ->and($file->fileId)->toBe('fid')
        ->and($file->fileUniqueId)->toBe('uniq');
});
