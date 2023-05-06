<?php
declare(strict_types=1);

namespace TranslationTest\Domain\Service;

use PHPUnit\Framework\TestCase;
use Translation\Domain\Service\SymfonyLocaleValidator;

final class SymfonyLocaleValidatorTest extends TestCase
{
    public function test_it_can_validate_a_locale()
    {
        $validator = new SymfonyLocaleValidator();

        $this->assertTrue($validator->validate('fr_FR'));
        $this->assertTrue($validator->validate('en_GB'));
        $this->assertTrue($validator->validate('it_IT'));
        $this->assertTrue($validator->validate('es_ES'));

        $this->assertFalse($validator->validate('en_EN'));
        $this->assertFalse($validator->validate('toto'));
    }
}
