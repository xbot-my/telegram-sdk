<?php

declare(strict_types=1);

namespace XBot\Telegram\Inline;

final class InlineKeyboard
{
    private array $rows = [];
    private int $currentRow = -1;

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

    // Backward-compatible aliases
    public function newRow(): self
    {
        return $this->row();
    }

    public function addButton(array $button): self
    {
        return $this->button($button);
    }

    public function toArray(): array
    {
        return ['inline_keyboard' => $this->rows];
    }
}
