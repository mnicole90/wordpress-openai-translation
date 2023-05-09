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
        add_action('admin_enqueue_scripts', [$this, 'enqueue_assets']);
        add_action('print_media_templates', [$this, 'add_tpl_translation_button']);
    }

    public function enqueue_assets(): void
    {
        global $pagenow;
        if (!in_array($pagenow, ['post.php', 'post-new.php'])) {
            return;
        }

        if (!current_user_can('edit_posts') || !current_user_can('edit_pages')) {
            return;
        }

        wp_register_script('translation.js', plugin_dir_url($this->file) . 'assets/js/translation.js', ['jquery'], '1.1.0');
        wp_enqueue_script('translation.js');
        wp_localize_script('translation.js', 'openai_translation', [
            'rest_url' => rest_url(TranslationPlugin::NAMESPACE . '/translate'),
        ]);

        // Add font awesome
        wp_enqueue_style('font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css', [], '5.15.3');
    }

    public function add_tpl_translation_button(): void
    {
        $this->render('translation-button');
    }
}
