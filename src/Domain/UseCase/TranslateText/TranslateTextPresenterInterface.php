<?php
declare(strict_types=1);

namespace Translation\Domain\UseCase\TranslateText;

interface TranslateTextPresenterInterface
{
    public function present(TranslateTextResponse $response): void;
}
