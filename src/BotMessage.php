<?php

declare(strict_types=1);

namespace XBot\Telegram;

/**
 * Fluent message builder used by Quick.
 */
class BotMessage
{
    protected TelegramBot $bot;

    protected int|string|null $chatId = null;
    protected ?string $parseMode = null; // 'HTML' | 'Markdown'
    protected bool $silent = false;
    protected ?array $keyboard = null; // ReplyMarkup array

    public function __construct(TelegramBot $bot)
    {
        $this->bot = $bot;
    }

    public function to(int|string $chatId): self
    {
        $this->chatId = $chatId;
        return $this;
    }

    public function html(): self
    {
        $this->parseMode = 'HTML';
        return $this;
    }

    public function markdown(): self
    {
        $this->parseMode = 'Markdown';
        return $this;
    }

    public function silent(bool $silent = true): self
    {
        $this->silent = $silent;
        return $this;
    }

    /**
        Set a reply keyboard/inline keyboard payload.
        Example: [[['text' => 'Button', 'callback_data' => 'cb']]]
    */
    public function keyboard(array $keyboard): self
    {
        $this->keyboard = $keyboard;
        return $this;
    }

    /**
     * Send a text message using collected options.
     */
    public function message(string $text): \XBot\Telegram\Models\DTO\Message
    {
        if ($this->chatId === null) {
            throw new \InvalidArgumentException('chatId is required. Call ->to($chatId) first.');
        }

        $options = [];
        if ($this->parseMode) {
            $options['parse_mode'] = $this->parseMode;
        }
        if ($this->silent) {
            $options['disable_notification'] = true;
        }
        if ($this->keyboard !== null) {
            $options['reply_markup'] = [
                // Auto-detect: if it looks like inline keyboard (callback_data entries), set accordingly
                // Consumers can pass full reply_markup if they need more control.
                'inline_keyboard' => $this->keyboard,
            ];
        }

        return $this->bot->sendMessage($this->chatId, $text, $options);
    }
}
