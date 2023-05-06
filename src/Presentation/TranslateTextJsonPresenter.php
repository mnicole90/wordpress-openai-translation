<?php
declare(strict_types=1);

namespace Translation\Presentation;

use Translation\Domain\UseCase\TranslateText\TranslateTextPresenterInterface;
use Translation\Domain\UseCase\TranslateText\TranslateTextResponse;

final class TranslateTextJsonPresenter implements TranslateTextPresenterInterface
{
    private array $json = [];

    public function present(TranslateTextResponse $response): void
    {
        $this->json = [
            'title' => $response->getTitle(),
            'blocks' => $response->getBlocks(),
        ];

        if ($response->hasErrors()) {
            $this->json['errors'] = $response->getErrors();
        }
    }


    public function json(): array
    {
        return $this->json;
    }

    public function hasErrors(): bool
    {
        return !empty($this->json['errors']);
    }
}
