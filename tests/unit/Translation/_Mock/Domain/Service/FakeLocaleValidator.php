<?php
declare(strict_types=1);

namespace TranslationTest\_Mock\Domain\Service;

use Translation\Domain\Service\LocaleValidatorInterface;

final class FakeLocaleValidator implements LocaleValidatorInterface
{

    public function validate(string $locale): bool
    {
        return in_array($locale, ['fr_FR', 'es_ES', 'en_US', 'en', 'fr', 'es']);
    }
}
