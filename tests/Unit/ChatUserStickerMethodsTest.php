<?php

declare(strict_types=1);

use XBot\Telegram\Bot;
use XBot\Telegram\Contracts\Http\Client as ClientContract;
use XBot\Telegram\Http\Response\TelegramResponse;

class FakeHttpClientCUS implements ClientContract
{
    public function get(string $method, array $parameters = []): TelegramResponse { return $this->post($method, $parameters); }
    public function post(string $method, array $parameters = []): TelegramResponse {
        $map = [
            'getChatMemberCount' => 7,
            'getChatMember' => [ 'user' => [ 'id' => $parameters['user_id'] ?? 0, 'is_bot' => false ] ],
            'setChatTitle' => true,
            'setChatDescription' => true,
            'setChatPermissions' => true,
            'pinChatMessage' => true,
            'unpinAllChatMessages' => true,
            'leaveChat' => true,
            'getUserProfilePhotos' => [ 'total_count' => 1, 'photos' => [] ],
            'getStickerSet' => [ 'name' => $parameters['name'] ?? 's', 'stickers' => [] ],
            'getCustomEmojiStickers' => [ [ 'file_id' => 'x' ] ],
            'uploadStickerFile' => [ 'file_id' => 'file123' ],
            'createNewStickerSet' => true,
            'addStickerToSet' => true,
            'setStickerSetTitle' => true,
            'setStickerEmojiList' => true,
            'setStickerKeywords' => true,
            'setStickerMaskPosition' => true,
            'replaceStickerInSet' => true,
            'deleteStickerFromSet' => true,
        ];
        return TelegramResponse::success($map[$method] ?? null);
    }
    public function upload(string $method, array $parameters = [], array $files = []): TelegramResponse { return $this->post($method, $parameters); }
    public function getToken(): string { return '000:CUS_______________________'; }
    public function getBaseUrl(): string { return 'https://api.telegram.org/bot'; }
    public function getConfig(): array { return []; }
    public function setTimeout(int $timeout): static { return $this; }
    public function setRetryAttempts(int $attempts): static { return $this; }
    public function setRetryDelay(int $delay): static { return $this; }
    public function getLastResponse(): ?TelegramResponse { return null; }
    public function getLastError(): ?Throwable { return null; }
}

it('covers chat management endpoints', function () {
    $bot = new Bot(new FakeHttpClientCUS(), ['name' => 't']);
    expect($bot->getChatMemberCount(1))->toBe(7);
    expect($bot->getChatMember(1, 123)->toArray()['user']['id'])->toBe(123);
    expect($bot->setChatTitle(1, 'New Title'))->toBeTrue();
    expect($bot->pinChatMessage(1, 10, ['disable_notification' => true]))->toBeTrue();
    expect($bot->leaveChat(1))->toBeTrue();
});

it('covers user profile photos', function () {
    $bot = new Bot(new FakeHttpClientCUS(), ['name' => 't']);
    expect($bot->getUserProfilePhotos(321)->toArray()['total_count'])->toBe(1);
});

it('covers stickers endpoints', function () {
    $bot = new Bot(new FakeHttpClientCUS(), ['name' => 't']);
    expect($bot->getStickerSet('funny')->toArray()['name'])->toBe('funny');
    expect($bot->getCustomEmojiStickers(['abc']))->toBeArray();
    expect($bot->createNewStickerSet(1, 'pack_by_bot', 'My Pack', [ [ 'sticker' => 'file.png', 'emoji_list' => ['😀'] ] ]))->toBeTrue();
    expect($bot->addStickerToSet(1, 'pack_by_bot', [ 'sticker' => 'file.png', 'emoji_list' => ['😀'] ]))->toBeTrue();
    expect($bot->setStickerSetTitle('pack_by_bot', 'My Pack 2'))->toBeTrue();
    expect($bot->setStickerEmojiList('sticker_id', ['😀']))->toBeTrue();
    expect($bot->setStickerKeywords('sticker_id', ['funny']))->toBeTrue();
    expect($bot->setStickerMaskPosition('sticker_id', [ 'point' => 'forehead', 'x_shift' => 0, 'y_shift' => 0, 'scale' => 1 ]))->toBeTrue();
    expect($bot->replaceStickerInSet(1, 'pack_by_bot', 'old', [ 'sticker' => 'file2.png', 'emoji_list' => ['😀'] ]))->toBeTrue();
    expect($bot->deleteStickerFromSet('sticker_id'))->toBeTrue();
});
