<?php
declare(strict_types=1);

namespace Translation\Wordpress;

final class Installation
{
    use RenderTemplate;

    const OPENAI_TRANSLATION_ACTIVATED = 'openai_translation_activated';

    public function __construct(string $file)
    {
        $this->file = $file;
        register_activation_hook($file, [$this, 'plugin_activation']);
        add_action('admin_notices', [$this, 'notices_activation']);
    }

    public function plugin_activation(): void
    {
        set_transient(self::OPENAI_TRANSLATION_ACTIVATED, true);
    }

    public function notices_activation(): void
    {
        if (get_transient(self::OPENAI_TRANSLATION_ACTIVATED)) {
            // Initialize all API keys from constants to WP options
            // This will only set options that don't already exist
            ApiKeyManager::initializeFromConstants();

            // Handle validator name
            $validatorName = get_option('openai_translation_validator_name');
            if (empty($validatorName)) {
                $defaultValidator = defined('OPENAI_TRANSLATION_VALIDATOR') 
                    ? OPENAI_TRANSLATION_VALIDATOR 
                    : 'custom';
                add_option('openai_translation_validator_name', $defaultValidator);
            }

            $this->render('notices', [
                'message' => 'Translation plugin is now activated!',
            ]);
            delete_transient(self::OPENAI_TRANSLATION_ACTIVATED);
        }
    }
}
