<?php
declare(strict_types=1);

namespace Translation\Wordpress;

/**
 * Manages API keys from both define() constants and WordPress options
 * Constants take priority over WP options
 */
final class ApiKeyManager
{
    /**
     * Backend configuration mapping
     * Maps backend identifier to its constant name and WP option name
     */
    private const BACKENDS = [
        'openai' => [
            'constant' => 'OPENAI_API_KEY',
            'option' => 'openai_translation_api_key',
            'name' => 'OpenAI'
        ],
        'deepl' => [
            'constant' => 'DEEPL_API_KEY',
            'option' => 'deepl_translation_api_key',
            'name' => 'DeepL'
        ],
        'grok' => [
            'constant' => 'GROK_API_KEY',
            'option' => 'grok_translation_api_key',
            'name' => 'Grok'
        ],
        'deepseek' => [
            'constant' => 'DEEPSEEK_API_KEY',
            'option' => 'deepseek_translation_api_key',
            'name' => 'Deepseek'
        ],
        'gemini' => [
            'constant' => 'GEMINI_API_KEY',
            'option' => 'gemini_translation_api_key',
            'name' => 'Gemini'
        ],
    ];

    /**
     * Get API key for a backend
     * Priority: define() constant > WP option
     * 
     * @param string $backend Backend identifier (openai, deepl, grok, etc.)
     * @return string|null API key or null if not configured
     */
    public static function getApiKey(string $backend): ?string
    {
        $config = self::BACKENDS[$backend] ?? null;
        if (!$config) {
            return null;
        }

        // Priority 1: Check if constant is defined
        if (defined($config['constant'])) {
            $key = constant($config['constant']);
            if (!empty($key)) {
                return $key;
            }
        }

        // Priority 2: Check WordPress option
        $key = get_option($config['option']);
        if (!empty($key)) {
            return $key;
        }

        return null;
    }

    /**
     * Set API key as WordPress option
     * 
     * @param string $backend Backend identifier
     * @param string $apiKey API key to store
     * @return bool Success status
     */
    public static function setApiKey(string $backend, string $apiKey): bool
    {
        $config = self::BACKENDS[$backend] ?? null;
        if (!$config) {
            return false;
        }

        return update_option($config['option'], $apiKey);
    }

    /**
     * Initialize API keys from constants to WP options on plugin activation
     * Only sets options if they don't already exist and constant is defined
     */
    public static function initializeFromConstants(): void
    {
        foreach (self::BACKENDS as $backend => $config) {
            // Skip if option already exists
            if (get_option($config['option']) !== false) {
                continue;
            }

            // Check if constant is defined and not empty
            if (defined($config['constant'])) {
                $key = constant($config['constant']);
                if (!empty($key)) {
                    add_option($config['option'], $key);
                }
            }
        }
    }

    /**
     * Get all available backends (those with API keys configured)
     * 
     * @return array Associative array of backend => name
     */
    public static function getAvailableBackends(): array
    {
        $available = [];

        // Google Translate is always available (no API key required)
        $available['google'] = 'Google Translate';

        foreach (self::BACKENDS as $backend => $config) {
            $apiKey = self::getApiKey($backend);
            if ($apiKey !== null) {
                $available[$backend] = $config['name'];
            }
        }

        return $available;
    }

    /**
     * Get list of all supported backends
     * 
     * @return array List of backend identifiers
     */
    public static function getSupportedBackends(): array
    {
        return array_keys(self::BACKENDS);
    }

    /**
     * Get backend configuration
     * 
     * @param string $backend Backend identifier
     * @return array|null Configuration array or null if not found
     */
    public static function getBackendConfig(string $backend): ?array
    {
        return self::BACKENDS[$backend] ?? null;
    }
}