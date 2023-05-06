<?php
declare(strict_types=1);

namespace Translation\Domain\Service;

interface LocaleValidatorInterface
{
    public function validate(string $locale): bool;
}
