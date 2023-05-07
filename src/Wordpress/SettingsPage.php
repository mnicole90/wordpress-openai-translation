<?php
declare(strict_types=1);

namespace Translation\Wordpress;

final class SettingsPage
{
    use RenderTemplate;

    function __construct(string $file)
    {
        $this->file = $file;
        add_action('admin_menu', [$this, 'add_settings_page']);
        add_action('admin_init', [$this, 'register_settings']);
    }

    public function add_settings_page(): void
    {
        add_options_page(
            'OpenAI Translation',
            'OpenAI Translation',
            'manage_options',
            'openai-translation',
            [$this, 'render_settings_page']
        );
    }

    public function render_settings_page(): void
    {
        $this->render('settings-page');
    }

    public function register_settings(): void
    {
        register_setting('openai-translation', 'openai_translation_api_key');
        register_setting('openai-translation', 'openai_translation_validator_name');
    }
}
