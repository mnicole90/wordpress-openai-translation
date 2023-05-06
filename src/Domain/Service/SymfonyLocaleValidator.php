<?php
declare(strict_types=1);

namespace Translation\Domain\Service;

use Symfony\Component\Validator\Constraints\Locale;
use Symfony\Component\Validator\Validation;

final class SymfonyLocaleValidator implements LocaleValidatorInterface
{
    public function validate(string $locale): bool
    {
        $validator = Validation::createValidator();
        $violations = $validator->validate($locale, [
            new Locale(),
        ]);

        return 0 === count($violations);
    }
}
