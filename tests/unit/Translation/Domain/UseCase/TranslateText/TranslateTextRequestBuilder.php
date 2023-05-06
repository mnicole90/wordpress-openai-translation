<?php
declare(strict_types=1);

namespace TranslationTest\Domain\UseCase\TranslateText;

use Translation\Domain\UseCase\TranslateText\TranslateTextRequest;

class TranslateTextRequestBuilder extends TranslateTextRequest
{
    const BLOCKS = [[
        'attributes' => [
            'content' => 'This text is in english!',
        ],
        'clientId' => '3cecf306-d0c0-4ed6-8db9-11dca7316778',
        'name' => 'core/paragraph',
        'originalContent' => '<p>This text is in english!</p>',
        'isValid' => true,
    ]];

    public static function spanishRequest(): static
    {
        $request = new static();
        $request->title = 'Hello spanish world';
        $request->blocks = self::BLOCKS;
        $request->targetLanguage = 'es_ES';

        return $request;
    }

    public static function frenchRequest(): static
    {
        $request = new static();
        $request->title = 'Hello french world';
        $request->blocks = self::BLOCKS;
        $request->targetLanguage = 'fr_FR';

        return $request;
    }

    public static function empty(): static
    {
        $request = new static();
        $request->title = '';
        $request->blocks = [];
        $request->targetLanguage = '';

        return $request;
    }

    public function build(): TranslateTextRequest
    {
        $request = new TranslateTextRequest();
        $request->title = $this->title;
        $request->blocks = $this->blocks;
        $request->targetLanguage = $this->targetLanguage;

        return $request;
    }

    public function withTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function withBlocks(array $blocks): static
    {
        $this->blocks = $blocks;

        return $this;
    }

    public function withTargetLanguage(string $targetLanguage): static
    {
        $this->targetLanguage = $targetLanguage;

        return $this;
    }
}
