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
            '1.1.0',
            false
        );

        // Localize script with REST API URL
        wp_localize_script('openai-translation-gutenberg-editor', 'openaiTranslation', [
            'restUrl' => rest_url(TranslationPlugin::NAMESPACE . '/translate'),
        ]);
    }
}
