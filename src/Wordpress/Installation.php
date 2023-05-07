<?php
declare(strict_types=1);

namespace Translation\Wordpress;

final class Installation
{
    use RenderTemplate;

    const OPENAI_TRANSLATION_ACTIVATED = 'openai_translation_activated';

    public function __construct(string $file, private readonly string $validatorName, private readonly string $apiKey)
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
            $apiKey = get_option('openai_translation_api_key');
            if (empty($apiKey) && empty($this->apiKey)) {
                $this->render('notices-empty-openaikey');
                return;
            } elseif (empty($apiKey)) {
                add_option('openai_translation_api_key', $this->apiKey);
            }

            $validatorName = get_option('openai_translation_validator_name');
            if (empty($validatorName)) {
                add_option('openai_translation_validator_name', $this->validatorName);
            }

            $this->render('notices', [
                'message' => 'OpenAI Translation plugin is now activated!',
            ]);
            delete_transient(self::OPENAI_TRANSLATION_ACTIVATED);
        }
    }
}
