<?php
declare(strict_types=1);

namespace Translation\Domain\UseCase\TranslateText;

class TranslateTextRequest
{
    public string $title;
    public array $blocks;
    public string $targetLanguage;
}
