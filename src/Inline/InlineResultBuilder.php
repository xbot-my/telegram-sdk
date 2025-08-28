<?php

declare(strict_types=1);

namespace XBot\Telegram\Inline;

/**
 * Helper to build InlineQueryResult items and InputMessageContent payloads.
 */
class InlineResultBuilder
{
    // InputMessageContent helpers
    public static function text(string $text, array $options = []): array
    {
        if ($text === '') {
            throw new \InvalidArgumentException('message_text cannot be empty');
        }
        return array_merge(['message_text' => $text], $options);
    }

    public static function markdown(string $text, array $options = []): array
    {
        return self::text($text, array_merge(['parse_mode' => 'Markdown'], $options));
    }

    public static function html(string $text, array $options = []): array
    {
        return self::text($text, array_merge(['parse_mode' => 'HTML'], $options));
    }

    // InlineQueryResult helpers
    public static function article(string $id, string $title, array $inputMessageContent, array $options = []): array
    {
        if ($id === '' || $title === '') {
            throw new \InvalidArgumentException('id and title are required');
        }
        if (empty($inputMessageContent['message_text'] ?? null)) {
            throw new \InvalidArgumentException('input_message_content.message_text is required');
        }
        return array_merge([
            'type' => 'article',
            'id' => $id,
            'title' => $title,
            'input_message_content' => $inputMessageContent,
        ], $options);
    }

    public static function photo(string $id, string $photoUrl, string $thumbUrl, array $options = []): array
    {
        self::assertUrl($photoUrl); self::assertUrl($thumbUrl);
        return array_merge([
            'type' => 'photo',
            'id' => $id,
            'photo_url' => $photoUrl,
            'thumbnail_url' => $thumbUrl,
        ], $options);
    }

    public static function gif(string $id, string $gifUrl, string $thumbUrl, array $options = []): array
    {
        self::assertUrl($gifUrl); self::assertUrl($thumbUrl);
        return array_merge([
            'type' => 'gif',
            'id' => $id,
            'gif_url' => $gifUrl,
            'thumbnail_url' => $thumbUrl,
        ], $options);
    }

    public static function mpeg4Gif(string $id, string $mpeg4Url, string $thumbUrl, array $options = []): array
    {
        self::assertUrl($mpeg4Url); self::assertUrl($thumbUrl);
        return array_merge([
            'type' => 'mpeg4_gif',
            'id' => $id,
            'mpeg4_url' => $mpeg4Url,
            'thumbnail_url' => $thumbUrl,
        ], $options);
    }

    public static function video(string $id, string $videoUrl, string $mimeType, string $title, string $thumbUrl, array $options = []): array
    {
        if ($title === '') { throw new \InvalidArgumentException('title is required'); }
        self::assertUrl($videoUrl); self::assertUrl($thumbUrl);
        return array_merge([
            'type' => 'video',
            'id' => $id,
            'video_url' => $videoUrl,
            'mime_type' => $mimeType,
            'title' => $title,
            'thumbnail_url' => $thumbUrl,
        ], $options);
    }

    protected static function assertUrl(string $url): void
    {
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            throw new \InvalidArgumentException('Invalid URL: ' . $url);
        }
    }
}

