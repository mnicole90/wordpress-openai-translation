<?php
declare(strict_types=1);

namespace Translation\Domain\UseCase\TranslateText;

final class TranslateTextResponse
{
    private array $errors = [];
    private string $title = '';
    private array $blocks = [];

    public function setTitle(string $translation): self
    {
        $this->title = $translation;

        return $this;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function addBlock(array $block): self
    {
        $this->blocks[] = $block;

        return $this;
    }

    public function getBlocks(): array
    {
        return $this->blocks;
    }

    public function addError(string $name, string $message): void
    {
        $this->errors[$name] = $message;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function hasErrors(): bool
    {
        return !empty($this->errors);
    }
}
