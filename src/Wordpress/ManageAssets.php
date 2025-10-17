<?php
declare(strict_types=1);

namespace Translation\Wordpress;

use Translation\TranslationPlugin;

final class ManageAssets
{
    use RenderTemplate;

    public function __construct(string $file)
    {
        $this->file = $file;
        add_action('enqueue_block_editor_assets', [$this, 'load_editor_assets'], 10);
    }

    public function load_editor_assets(): void
    {
        // Main sidebar plugin
        wp_enqueue_script(
            'openai-translation-gutenberg-editor',
            plugin_dir_url($this->file) . 'assets/build/editor.js',
            [
                'wp-i18n',
                'wp-element',
                'wp-blocks',
                'wp-components',
                'wp-editor',
                'wp-edit-post',
                'wp-plugins',
                'wp-data',
                'wp-compose',
                'wp-api-fetch'
            ],
            '1.2.0',
            false
        );

        // Block-level controls (translation and rollback buttons)
        wp_enqueue_script(
            'openai-translation-block-controls',
            plugin_dir_url($this->file) . 'assets/build/block-editor.js',
            [
                'wp-i18n',
                'wp-element',
                'wp-blocks',
                'wp-components',
                'wp-block-editor',
                'wp-data',
                'wp-hooks',
                'wp-compose',
                'wp-api-fetch'
            ],
            '1.2.0',
            false
        );

        // Pass available backends, languages, supported blocks, and REST API namespace to JS
        // Make config available to all scripts
        $config = [
            'restNamespace' => TranslationPlugin::NAMESPACE,
            'backends' => TranslationPlugin::getAvailableBackends(),
            'languages' => TranslationPlugin::getLanguageList(),
            'supportedBlocks' => TranslationPlugin::getSupportedBlockTypes(),
        ];

        wp_localize_script('openai-translation-gutenberg-editor', 'translationConfig', $config);
        wp_localize_script('openai-translation-block-controls', 'translationConfig', $config);
        wp_enqueue_style('openai-translation-gutenberg-editor-css', plugin_dir_url($this->file) . 'assets/editor-style.css');
    }
}
