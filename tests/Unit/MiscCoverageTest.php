<?php

declare(strict_types=1);

use XBot\Telegram\Bot;
use XBot\Telegram\Contracts\Http\Client as ClientContract;
use XBot\Telegram\Http\Response\TelegramResponse;

class FakeHttpClientMisc implements ClientContract
{
    public function get(string $method, array $parameters = []): TelegramResponse { return $this->post($method, $parameters); }
    public function post(string $method, array $parameters = []): TelegramResponse {
        $map = [
            'exportChatInviteLink' => 'https://t.me/+abcdef',
            'approveChatJoinRequest' => true,
            'declineChatJoinRequest' => true,
            'setChatStickerSet' => true,
            'deleteChatStickerSet' => true,
            'editMessageCaption' => [ 'ok' => true ],
            'editMessageLiveLocation' => [ 'ok' => true ],
            'stopMessageLiveLocation' => [ 'ok' => true ],
            'sendGame' => [ 'message_id' => 5, 'game' => ['title' => 'g'] ],
            'setGameScore' => true,
            'getGameHighScores' => [ [ 'user' => [ 'id' => 1 ], 'score' => 10 ] ],
            'getForumTopicIconStickers' => [ [ 'file_id' => 'st1' ] ],
            'setStickerPositionInSet' => true,
            'setMessageReaction' => true,
        ];
        return TelegramResponse::success($map[$method] ?? null);
    }
    public function upload(string $method, array $parameters = [], array $files = []): TelegramResponse { return $this->post($method, $parameters); }
    public function getToken(): string { return '000:MISC_____'; }
    public function getBaseUrl(): string { return 'https://api.telegram.org/bot'; }
    public function getConfig(): array { return []; }
    public function setTimeout(int $timeout): static { return $this; }
    public function setRetryAttempts(int $attempts): static { return $this; }
    public function setRetryDelay(int $delay): static { return $this; }
    public function getLastResponse(): ?TelegramResponse { return null; }
    public function getLastError(): ?Throwable { return null; }
}

it('covers various chat and game endpoints', function () {
    $bot = new Bot(new FakeHttpClientMisc(), ['name' => 't']);
    expect($bot->exportChatInviteLink(1))->toBeString();
    expect($bot->approveChatJoinRequest(1, 2))->toBeTrue();
    expect($bot->declineChatJoinRequest(1, 2))->toBeTrue();
    expect($bot->setChatStickerSet(1, 'funny'))->toBeTrue();
    expect($bot->deleteChatStickerSet(1))->toBeTrue();
    expect($bot->editMessageCaption(['chat_id' => 1, 'message_id' => 10, 'caption' => 'c'])->toArray())->toBeArray();
    expect($bot->editMessageLiveLocation(1.0, 2.0, ['chat_id' => 1, 'message_id' => 11])->toArray())->toBeArray();
    expect($bot->stopMessageLiveLocation(['chat_id' => 1, 'message_id' => 11])->toArray())->toBeArray();
    expect($bot->sendGame(1, 'g')->toArray()['message_id'])->toBe(5);
    expect($bot->setGameScore(1, 100, ['chat_id' => 1, 'message_id' => 5])->toArray())->toBeArray();
    expect($bot->getGameHighScores(1, ['chat_id' => 1, 'message_id' => 5])->toArray())->toBeArray();
    expect($bot->getForumTopicIconStickers()->toArray())->toBeArray();
    expect($bot->setStickerPositionInSet('st', 0))->toBeTrue();
    expect($bot->setMessageReaction(1, 5, [['type' => 'emoji', 'emoji' => '👍']]))->toBeTrue();
});

