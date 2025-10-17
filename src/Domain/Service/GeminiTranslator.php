<?php
declare(strict_types=1);

namespace Translation\Domain\Service;

use RuntimeException;

final class GeminiTranslator extends AbstractAITranslator
{
    private const API_URL = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash-exp:generateContent';

    public function __construct(private readonly string $apiKey)
    {
    }

    protected function getEngineIdentifier(): string
    {
        return 'gemini';
    }

    public function translate(string $text, string $targetLocale): string
    {
        // Use inherited buildPrompt method
        $prompt = $this->buildPrompt($targetLocale);

        $payload = [
            'contents' => [
                [
                    'parts' => [
                        [
                            'text' => $prompt . "\n\n" . $text
                        ]
                    ]
                ]
            ]
        ];

        $response = $this->makeRequest($payload);

        if (!$response) {
            throw new RuntimeException('Gemini API error: No response');
        }

        $decodedResponse = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new RuntimeException('Gemini API error: Invalid JSON response');
        }

        if (isset($decodedResponse['error'])) {
            throw new RuntimeException('Gemini API error: ' . ($decodedResponse['error']['message'] ?? 'Unknown error'));
        }

        if (!isset($decodedResponse['candidates'][0]['content']['parts'][0]['text'])) {
            throw new RuntimeException('Gemini API error: Invalid response structure');
        }

        return trim($decodedResponse['candidates'][0]['content']['parts'][0]['text']);
    }

    private function makeRequest(array $payload): string|false
    {
        // Gemini uses API key as query parameter
        $url = self::API_URL . '?key=' . $this->apiKey;

        $ch = curl_init($url);

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
            ],
            CURLOPT_TIMEOUT => 60,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);

        if ($httpCode !== 200) {
            throw new RuntimeException("Gemini API error: HTTP $httpCode");
        }

        return $response;
    }
}