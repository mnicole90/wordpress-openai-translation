<?php
declare(strict_types=1);

namespace TranslationTest\_Mock\Domain\Service;

use Translation\Domain\Service\TranslatorInterface;

final class FakeTranslator implements TranslatorInterface
{
    public function translate(string $text, string $targetLanguage): string
    {
        return match ($targetLanguage) {
            'fr_FR' => 'Bonjour',
            'es_ES' => 'Hola',
        };
    }
}
