<?php
declare(strict_types=1);

namespace Translation\Domain\UseCase\TranslateText;

use Translation\Domain\Service\LocaleValidatorInterface;
use Translation\Domain\Service\TranslatorInterface;

final readonly class TranslateText
{
    public function __construct(
        private TranslatorInterface      $translator,
        private LocaleValidatorInterface $localeValidator
    )
    {
    }

    public function execute(TranslateTextRequest $request, TranslateTextPresenterInterface $presenter): void
    {
        $response = new TranslateTextResponse();

        $this->translateText($request, $response);

        $presenter->present($response);
    }

    private function validateRequest(TranslateTextRequest $request, TranslateTextResponse $response): bool
    {
        // Require at least one of: title or blocks
        $hasTitle = !empty($request->title);
        $hasBlocks = !empty($request->blocks);

        if (!$hasTitle && !$hasBlocks) {
            $response->addError('content', 'form.content.required');
        }

        if (empty($request->targetLanguage)) {
            $response->addError('targetLanguage', 'form.targetLanguage.required');
        }
        return !$response->hasErrors();
    }

    private function validateLanguage(TranslateTextRequest $request, TranslateTextResponse $response): bool
    {
        if (!$this->localeValidator->validate($request->targetLanguage)) {
            $response->addError('targetLanguage', 'form.targetLanguage.not_supported');
        }
        return !$response->hasErrors();
    }

    private function translateText(TranslateTextRequest $request, TranslateTextResponse $response): void
    {
        if (!$this->validateRequest($request, $response)) {
            return;
        }

        if (!$this->validateLanguage($request, $response)) {
            return;
        }

        // Use targetLocale for semantic clarity (it's a locale code like 'en_US')
        $targetLocale = $request->targetLanguage;

        // Translate title if provided
        if (!empty($request->title)) {
            $this->translateTitle($request->title, $targetLocale, $response);
        }

        // Translates the text of each block (recursively handles nested blocks)
        // Skip if blocks array is empty (title-only translation)
        if (!empty($request->blocks)) {
            array_map(fn($block) => $this->translateBlock($block, $targetLocale, $response), $request->blocks);
        }
    }

    /**
     * Get default block-to-attributes mapping
     * Returns array mapping block names to their translatable attributes
     *
     * @return array Array mapping block names to attribute arrays
     */
    private static function getDefaultBlockAttributesMap(): array
    {
        return [
            // Text blocks - 'content' attribute
            'core/paragraph' => ['content'],
            'core/heading' => ['content'],
            'core/verse' => ['content'],
            'core/preformatted' => ['content'],
            'core/code' => ['content'],
            'core/list-item' => ['content'],

            // List blocks - 'values' attribute
            'core/list' => ['values'],

            // Button blocks
            'core/button' => ['text'],
            'core/buttons' => [], // Container only, no direct content

            // Quote blocks - multiple attributes
            'core/quote' => ['value', 'citation'],
            'core/pullquote' => ['value', 'citation'],

            // Media blocks - alt text and captions
            'core/image' => ['alt', 'caption'],
            'core/gallery' => ['caption'],
            'core/video' => ['caption'],
            'core/audio' => ['caption'],
            'core/file' => ['fileName'],

            // Layout/Container blocks - no direct content, only innerBlocks
            'core/group' => [],
            'core/columns' => [],
            'core/column' => [],
            'core/cover' => [],
            'core/media-text' => [],
            'core/row' => [],
            'core/stack' => [],

            // Table - complex structure, not yet supported
            // 'core/table' => [],
        ];
    }

    /**
     * Get translatable attributes for a given block type
     * Returns array of attribute names that contain translatable content
     *
     * @param string $blockName The block type name (e.g., 'core/paragraph')
     * @return array Array of attribute names that should be translated
     */
    private function getTranslatableAttributes(string $blockName): array
    {
        $blockAttributesMap = self::getDefaultBlockAttributesMap();
        $defaultAttributes = $blockAttributesMap[$blockName] ?? ['content'];

        /**
         * Filter translatable attributes for a block type
         *
         * Allows themes and plugins to add or modify translatable attributes for any block type.
         *
         * @since 1.2.0
         *
         * @param array  $attributes Array of attribute names to translate
         * @param string $blockName  The block type name (e.g., 'core/paragraph')
         *
         * @example
         * // Add custom block translation support
         * add_filter('openai_translation_block_attributes', function($attributes, $blockName) {
         *     if ($blockName === 'my-plugin/custom-block') {
         *         return ['title', 'description'];
         *     }
         *     return $attributes;
         * }, 10, 2);
         */
        return apply_filters('openai_translation_block_attributes', $defaultAttributes, $blockName);
    }

    /**
     * Get all supported block types (public static for frontend access)
     * Returns array of block names that have translatable content
     * Reuses the block list from getDefaultBlockAttributesMap() to avoid duplication
     *
     * @return array Array of supported block type names
     */
    public static function getSupportedBlockTypes(): array
    {
        $blockAttributesMap = self::getDefaultBlockAttributesMap();
        $supportedBlocks = array_keys($blockAttributesMap);

        /**
         * Filter supported block types for translation
         *
         * Allows themes and plugins to add custom block types to the translation system.
         * When adding custom blocks, also use the 'openai_translation_block_attributes' filter
         * to specify which attributes should be translated.
         *
         * @since 1.2.0
         *
         * @param array $supportedBlocks Array of block type names
         *
         * @example
         * // Add custom block type
         * add_filter('openai_translation_supported_blocks', function($blocks) {
         *     $blocks[] = 'my-plugin/custom-block';
         *     return $blocks;
         * });
         *
         * // Then specify its translatable attributes
         * add_filter('openai_translation_block_attributes', function($attributes, $blockName) {
         *     if ($blockName === 'my-plugin/custom-block') {
         *         return ['title', 'description'];
         *     }
         *     return $attributes;
         * }, 10, 2);
         */
        return apply_filters('openai_translation_supported_blocks', $supportedBlocks);
    }

    public function translateBlock(array $block, string $targetLocale, TranslateTextResponse $response): void
    {
        $blockName = $block['name'] ?? '';
        $attributes = $block['attributes'] ?? [];
        $innerBlocks = $block['innerBlocks'] ?? [];

        // Get translatable attributes for this block type
        $translatableAttrs = $this->getTranslatableAttributes($blockName);

        // Translate each translatable attribute
        foreach ($translatableAttrs as $attrName) {
            $content = $attributes[$attrName] ?? '';

            if (!empty($content)) {
                $translation = $this->translator->translate(
                    text: $content,
                    targetLocale: $targetLocale
                );

                if (!$translation) {
                    $response->addError('internal', 'internal.error.translation_failed');
                    return;
                }

                // Update block with translated content
                $block['attributes'][$attrName] = $translation;
            }
        }

        // Recursively translate inner blocks (for groups, columns, etc.)
        if (!empty($innerBlocks)) {
            $translatedInnerBlocks = [];
            foreach ($innerBlocks as $innerBlock) {
                $innerResponse = new TranslateTextResponse();
                $this->translateBlock($innerBlock, $targetLocale, $innerResponse);

                // Get the translated inner block
                $translated = $innerResponse->getBlocks();
                if (!empty($translated)) {
                    $translatedInnerBlocks[] = $translated[0];
                }
            }
            $block['innerBlocks'] = $translatedInnerBlocks;
        }

        // Add the (possibly updated) block to response
        $response->addBlock($block);
    }

    private function translateTitle(string $title, string $targetLocale, TranslateTextResponse $response): void
    {
        $title = $this->translator->translate(
            text: $title,
            targetLocale: $targetLocale
        );

        if (!$title) {
            $response->addError('internal', 'internal.error.translation_failed');
            return;
        }

        $response->setTitle($title);
    }
}
