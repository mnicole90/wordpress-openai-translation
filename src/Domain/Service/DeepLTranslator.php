<?php
declare(strict_types=1);

namespace Translation\Domain\Service;

use RuntimeException;

final class DeepLTranslator implements TranslatorInterface
{
    private const API_URL_FREE = 'https://api-free.deepl.com/v2/translate';
    private const API_URL_PRO = 'https://api.deepl.com/v2/translate';

    public function __construct(private readonly ?string $apiKey = null)
    {
    }

    public function translate(string $text, string $targetLocale): string
    {
        if (!$this->apiKey) {
            throw new RuntimeException('DeepL API key is required');
        }

        // Convert WordPress locale format to DeepL format (e.g., en_US -> EN-US, en_GB -> EN-GB)
        $targetLang = $this->convertLocaleToDeepLCode($targetLocale);

        // Determine API URL based on key type (free keys end with :fx)
        $apiUrl = str_ends_with($this->apiKey, ':fx') ? self::API_URL_FREE : self::API_URL_PRO;
        $body = [
            'text' => $text,
            'target_lang' => $targetLang,
        ];

        $response = wp_remote_post($apiUrl, [
            'timeout' => 30,
            'headers' => [
                'Authorization' => 'DeepL-Auth-Key ' . $this->apiKey,
                'Content-Type' => 'application/x-www-form-urlencoded',
            ],
            'body' => $body
        ]);

        do_action('openai_translation_http_response', $response, $apiUrl, [], $body);

        if (is_wp_error($response)) {
            throw new RuntimeException('DeepL API error: ' . $response->get_error_message());
        }

        $statusCode = wp_remote_retrieve_response_code($response);
        if ($statusCode !== 200) {
            $body = wp_remote_retrieve_body($response);
            throw new RuntimeException('DeepL API error (HTTP ' . $statusCode . '): ' . $body);
        }

        $body = wp_remote_retrieve_body($response);
        $decoded = json_decode($body, true);

        if (!isset($decoded['translations'][0]['text'])) {
            throw new RuntimeException('Invalid response from DeepL');
        }

        return $decoded['translations'][0]['text'];
    }

    private function convertLocaleToDeepLCode(string $locale): string
    {
        // DeepL uses language codes like EN, DE, FR, ES, IT, JA, etc.
        // For some languages it supports variants like EN-GB, EN-US, PT-BR, PT-PT

        $mapping = [
            'en_GB' => 'EN-GB',
            'en_US' => 'EN-US',
            'pt_BR' => 'PT-BR',
            'pt_PT' => 'PT-PT',
        ];

        if (isset($mapping[$locale])) {
            return $mapping[$locale];
        }

        // Default: use the first two characters in uppercase
        return strtoupper(substr($locale, 0, 2));
    }
}
