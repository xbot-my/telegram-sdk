<?php

declare(strict_types=1);

use XBot\Telegram\Utils\FileHelper as F;

it('gets extensions and media type helpers', function () {
    expect(F::getExtension('photo.JPG'))->toBe('jpg')
        ->and(F::isImage('x.png'))->toBeTrue()
        ->and(F::isVideo('x.mp4'))->toBeTrue()
        ->and(F::isAudio('x.mp3'))->toBeTrue()
        ->and(F::isAudio('x.unknown'))->toBeFalse();
});

it('works with temp files: write, read, copy, move, delete', function () {
    $dir = sys_get_temp_dir() . '/tg_' . uniqid();
    $src = $dir . '/a.txt';
    $dst = $dir . '/sub/b.txt';
    expect(F::ensureDirectory($dir))->toBeTrue();

    expect(F::writeFile($src, 'hello'))->toBeTrue();
    expect(file_exists($src))->toBeTrue();
    expect(F::readFile($src))->toBe('hello');

    expect(F::copyFile($src, $dst))->toBeTrue();
    expect(file_exists($dst))->toBeTrue();

    $moved = $dir . '/moved.txt';
    expect(F::moveFile($dst, $moved))->toBeTrue();
    expect(file_exists($moved))->toBeTrue();

    expect(F::deleteFile($src))->toBeTrue();
    expect(file_exists($src))->toBeFalse();
    expect(F::deleteFile($moved))->toBeTrue();
});

it('sanitizes and generates unique filenames', function () {
    $name = F::sanitizeFileName('weird*name?.txt');
    expect($name)->toBe('weird_name_.txt');

    $dir = sys_get_temp_dir() . '/tg_' . uniqid();
    F::ensureDirectory($dir);
    $existing = $dir . '/report.txt';
    file_put_contents($existing, 'x');

    $gen = F::generateUniqueFileName('report.txt', $dir);
    expect($gen)->toBe('report_1.txt');
});

