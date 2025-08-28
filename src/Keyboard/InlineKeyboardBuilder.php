<?php

declare(strict_types=1);

namespace XBot\Telegram\Keyboard;

class InlineKeyboardBuilder
{
    protected array $rows = [];

    public static function make(): self
    {
        return new self();
    }

    public function row(array ...$buttons): self
    {
        if (empty($buttons)) {
            throw new \InvalidArgumentException('Inline keyboard row cannot be empty');
        }
        $this->rows[] = $buttons;
        return $this;
    }

    public static function button(string $text, array $options): array
    {
        if ($text === '') {
            throw new \InvalidArgumentException('Button text cannot be empty');
        }
        $btn = array_merge(['text' => $text], $options);
        $actions = ['url','callback_data','web_app','login_url','switch_inline_query','switch_inline_query_current_chat'];
        $has = false;
        foreach ($actions as $a) { if (isset($btn[$a])) { $has = true; break; } }
        if (!$has) {
            throw new \InvalidArgumentException('Inline button requires at least one action (e.g., callback_data or url)');
        }
        return $btn;
    }

    public function toArray(): array
    {
        if (empty($this->rows)) {
            throw new \InvalidArgumentException('Inline keyboard cannot be empty');
        }
        return ['inline_keyboard' => $this->rows];
    }
}

