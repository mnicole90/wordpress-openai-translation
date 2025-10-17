<?php
declare(strict_types=1);

namespace Translation\Domain\Service;

use Translation\TranslationPlugin;

/**
 * Abstract base class for AI-based translators
 * Provides common prompt building functionality
 */
abstract class AbstractAITranslator implements TranslatorInterface
{
    /**
     * Base prompt template for translation
     * Uses %s placeholder for target language name
     */
    protected const BASE_PROMPT = 'You are a professional translator. Ensure translations are grammatically correct, natural-sounding, and human-oriented.';
    protected const PROMPT_SUFFIX = ' You will have to answer just by giving only one translation in %s.';

    /**
     * Get the engine identifier for this translator
     * Used in filter hooks and logging
     */
    abstract protected function getEngineIdentifier(): string;

    /**
     * Build translation prompt with filter support
     *
     * Converts locale to human-readable language name and applies WordPress filters
     *
     * @param string $targetLocale Locale code (e.g., 'en_US', 'fr_FR')
     * @return string Formatted prompt ready to use
     */
    protected function buildPrompt(string $targetLocale): string
    {
        // Convert locale (e.g., 'en_US') to human-readable language name (e.g., 'English (US)')
        $targetLanguage = TranslationPlugin::localeToLanguage($targetLocale);

        // Get base prompt template (can be overridden in child classes)
        $promptTemplate = static::BASE_PROMPT;

        // Apply WordPress filter to allow customization of the prompt
        // Filter parameters: $promptTemplate, $targetLocale, $targetLanguage, $engine
        $promptTemplate = apply_filters(
            'openai_translation_prompt',
            $promptTemplate,
            $targetLocale,
            $targetLanguage,
            $this->getEngineIdentifier()
        );

        // Insert language name into prompt template
        return $promptTemplate . sprintf(static::PROMPT_SUFFIX, $targetLanguage);
    }
}
