<?php

declare(strict_types=1);

namespace XBot\Telegram\Keyboard;

class ReplyKeyboardBuilder
{
    protected array $rows = [];
    protected bool $resize = false;
    protected bool $oneTime = false;
    protected bool $persistent = false;
    protected bool $selective = false;
    protected ?string $placeholder = null;

    public static function make(): self
    {
        return new self();
    }

    public function row(array ...$buttons): self
    {
        if (empty($buttons)) {
            throw new \InvalidArgumentException('Reply keyboard row cannot be empty');
        }
        $this->rows[] = $buttons;
        return $this;
    }

    public static function button(string $text, array $options = []): array
    {
        if ($text === '') {
            throw new \InvalidArgumentException('Button text cannot be empty');
        }
        return array_merge(['text' => $text], $options);
    }

    public function resize(bool $on = true): self { $this->resize = $on; return $this; }
    public function oneTime(bool $on = true): self { $this->oneTime = $on; return $this; }
    public function persistent(bool $on = true): self { $this->persistent = $on; return $this; }
    public function selective(bool $on = true): self { $this->selective = $on; return $this; }
    public function placeholder(?string $text): self { $this->placeholder = $text; return $this; }

    public function toArray(): array
    {
        if (empty($this->rows)) {
            throw new \InvalidArgumentException('Reply keyboard cannot be empty');
        }
        $arr = ['keyboard' => $this->rows];
        if ($this->resize) $arr['resize_keyboard'] = true;
        if ($this->oneTime) $arr['one_time_keyboard'] = true;
        if ($this->persistent) $arr['is_persistent'] = true;
        if ($this->selective) $arr['selective'] = true;
        if ($this->placeholder !== null) $arr['input_field_placeholder'] = $this->placeholder;
        return $arr;
    }

    public static function remove(bool $selective = false): array
    {
        return ['remove_keyboard' => true, 'selective' => $selective];
    }

    public static function forceReply(bool $selective = false, ?string $placeholder = null): array
    {
        $arr = ['force_reply' => true, 'selective' => $selective];
        if ($placeholder !== null) $arr['input_field_placeholder'] = $placeholder;
        return $arr;
    }
}

