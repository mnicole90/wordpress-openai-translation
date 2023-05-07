<?php
declare(strict_types=1);

namespace Translation;

use Translation\Wordpress\Endpoints;
use Translation\Wordpress\Installation;
use Translation\Wordpress\ManageAssets;
use Translation\Wordpress\SettingsPage;

final readonly class TranslationPlugin
{
    const NAMESPACE = 'openai-translation/v1';

    public function __construct(
        private string $file,
        private string $apiKey,
        private string $validatorName
    )
    {
        new Installation($this->file, $this->validatorName, $this->apiKey);
        new SettingsPage($this->file);
        new Endpoints();
        new ManageAssets($this->file);
    }
}
