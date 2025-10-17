<?php
declare(strict_types=1);

namespace Translation\Domain\Service;

use RuntimeException;

final class GoogleTranslator implements TranslatorInterface
{
    private const API_URL = 'https://translate.googleapis.com/translate_a/single';

    public function translate(string $text, string $targetLocale): string
    {
        // Convert WordPress locale format (e.g., en_US) to Google's format (e.g., en)
        $langCode = $this->convertLocaleToLanguageCode($targetLocale);

        $queryParams = http_build_query([
            'client' => 'gtx',
            'sl' => 'auto',
            'tl' => $langCode,
            'dt' => 't',
            'q' => $text,
        ]);

        $url = self::API_URL . '?' . $queryParams;

        $queryParams = apply_filters('openai_translation_http_request_fields', $queryParams, $url, []);

        $response = wp_remote_get($url, [
            'timeout' => 30,
            'headers' => [
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
            ]
        ]);

        do_action('openai_translation_http_response', $response, $url, [], $queryParams);

        if (is_wp_error($response)) {
            throw new RuntimeException('Google Translate API error: ' . $response->get_error_message());
        }

        $body = wp_remote_retrieve_body($response);
        $decoded = json_decode($body, true);

        if (!isset($decoded[0]) || !is_array($decoded[0])) {
            throw new RuntimeException('Invalid response from Google Translate');
        }

        $translation = '';
        foreach ($decoded[0] as $sentence) {
            if (isset($sentence[0])) {
                $translation .= $sentence[0];
            }
        }

        return trim($translation);
    }

    private function convertLocaleToLanguageCode(string $locale): string
    {
        // Convert locale format like en_US, fr_FR to just the language code
        return strtolower(substr($locale, 0, 2));
    }
}
