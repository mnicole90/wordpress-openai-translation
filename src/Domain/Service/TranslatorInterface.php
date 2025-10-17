<?php
declare(strict_types=1);

namespace Translation\Domain\Service;

interface TranslatorInterface
{
    public function translate(string $text, string $targetLocale): string;
}
