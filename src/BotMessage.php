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
    protected ?array $keyboard = null; // Inline keyboard (inline_keyboard)
    protected ?array $replyKeyboard = null; // Reply keyboard (keyboard)
    protected bool $removeKeyboard = false;
    protected bool $forceReply = false;
    protected ?string $inputFieldPlaceholder = null;
    protected bool $selective = false;

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
    /**
     * Set inline keyboard rows. Accepts array or InlineKeyboardBuilder.
     */
    public function keyboard(array|\XBot\Telegram\Keyboard\InlineKeyboardBuilder $keyboard): self
    {
        if ($keyboard instanceof \XBot\Telegram\Keyboard\InlineKeyboardBuilder) {
            $this->keyboard = $keyboard->toArray()['inline_keyboard'] ?? [];
        } else {
            $this->keyboard = $keyboard;
        }
        return $this;
    }

    /**
     * Alias of keyboard(): set inline keyboard.
     */
    public function inlineKeyboard(array $keyboard): self
    {
        return $this->keyboard($keyboard);
    }

    /**
     * Set a ReplyKeyboardMarkup.
     * Example rows: [[['text' => 'Yes']], [['text' => 'No']]]
     * Options: resize_keyboard, one_time_keyboard, is_persistent, selective, input_field_placeholder
     */
    /**
     * Set a ReplyKeyboardMarkup from rows or builder.
     */
    public function replyKeyboard(array|\XBot\Telegram\Keyboard\ReplyKeyboardBuilder $keyboard, array $options = []): self
    {
        if ($keyboard instanceof \XBot\Telegram\Keyboard\ReplyKeyboardBuilder) {
            $this->replyKeyboard = $keyboard->toArray();
        } else {
            $this->replyKeyboard = [
                'keyboard' => $keyboard,
            ] + $options;
        }
        return $this;
    }

    /**
     * Remove the custom keyboard.
     */
    public function removeKeyboard(bool $selective = false): self
    {
        $this->removeKeyboard = true;
        $this->selective = $selective;
        return $this;
    }

    /**
     * Force a reply from the user.
     */
    public function forceReply(bool $selective = false, ?string $placeholder = null): self
    {
        $this->forceReply = true;
        $this->selective = $selective;
        $this->inputFieldPlaceholder = $placeholder;
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
        // Inline keyboard
        if ($this->keyboard !== null) {
            $options['reply_markup'] = ['inline_keyboard' => $this->keyboard];
        }

        // Reply keyboard
        if ($this->replyKeyboard !== null) {
            $options['reply_markup'] = $this->replyKeyboard;
        }

        // Remove keyboard
        if ($this->removeKeyboard) {
            $options['reply_markup'] = ['remove_keyboard' => true, 'selective' => $this->selective];
        }

        // Force reply
        if ($this->forceReply) {
            $rm = ['force_reply' => true, 'selective' => $this->selective];
            if ($this->inputFieldPlaceholder !== null) {
                $rm['input_field_placeholder'] = $this->inputFieldPlaceholder;
            }
            $options['reply_markup'] = $rm;
        }

        return $this->bot->message->sendMessage($this->chatId, $text, $options);
    }
}
