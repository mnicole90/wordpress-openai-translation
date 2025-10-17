<?php
declare(strict_types=1);

namespace TranslationTest\_Mock\Domain\Service;

use Translation\Domain\Service\TranslatorInterface;

final class FakeTranslator implements TranslatorInterface
{
    public function translate(string $text, string $targetLocale): string
    {
        return match ($targetLocale) {
            'fr_FR' => 'Bonjour',
            'es_ES' => 'Hola',
        };
    }
}
