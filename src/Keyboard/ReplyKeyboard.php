<?php

declare(strict_types=1);

namespace XBot\Telegram\Keyboard;

final class ReplyKeyboard
{
    private array $rows = [];
    private int $currentRow = -1;

    private bool $resize = false;
    private bool $oneTime = false;
    private bool $persistent = false;
    private ?string $placeholder = null;
    private bool $selective = false;

    public static function make(): self
    {
        return new self();
    }

    public function row(): self
    {
        $this->rows[] = [];
        $this->currentRow = count($this->rows) - 1;
        return $this;
    }

    public function button(array $button): self
    {
        if ($this->currentRow === -1) {
            $this->row();
        }
        $this->rows[$this->currentRow][] = $button;
        return $this;
    }

    public function resize(bool $value = true): self
    {
        $this->resize = $value;
        return $this;
    }

    public function once(bool $value = true): self
    {
        $this->oneTime = $value;
        return $this;
    }

    // Backward-compatible aliases
    public function newRow(): self
    {
        return $this->row();
    }

    public function addButton(array $button): self
    {
        return $this->button($button);
    }

    public function oneTime(bool $value = true): self
    {
        return $this->once($value);
    }

    public function persistent(bool $value = true): self
    {
        $this->persistent = $value;
        return $this;
    }

    public function placeholder(?string $text): self
    {
        $this->placeholder = $text;
        return $this;
    }

    public function selective(bool $value = true): self
    {
        $this->selective = $value;
        return $this;
    }

    public function toArray(): array
    {
        $out = [
            'keyboard' => $this->rows,
        ];
        if ($this->resize) $out['resize_keyboard'] = true;
        if ($this->oneTime) $out['one_time_keyboard'] = true;
        if ($this->persistent) $out['is_persistent'] = true;
        if ($this->placeholder !== null) $out['input_field_placeholder'] = $this->placeholder;
        if ($this->selective) $out['selective'] = true;
        return $out;
    }
}
