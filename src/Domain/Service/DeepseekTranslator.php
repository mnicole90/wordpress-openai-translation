<?php
declare(strict_types=1);

namespace Translation\Domain\Service;

use RuntimeException;

final class DeepseekTranslator extends AbstractAITranslator
{
    private const API_URL = 'https://api.deepseek.com/chat/completions';

    public function __construct(private readonly string $apiKey)
    {
    }

    protected function getEngineIdentifier(): string
    {
        return 'deepseek';
    }

    public function translate(string $text, string $targetLocale): string
    {
        // Use inherited buildPrompt method
        $prompt = $this->buildPrompt($targetLocale);

        $payload = [
            'model' => 'deepseek-chat',
            'messages' => [
                [
                    'role' => 'user',
                    'content' => $prompt . "\n\n" . $text
                ]
            ]
        ];

        $response = $this->makeRequest($payload);

        if (!$response) {
            throw new RuntimeException('Deepseek API error: No response');
        }

        $decodedResponse = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new RuntimeException('Deepseek API error: Invalid JSON response');
        }

        if (isset($decodedResponse['error'])) {
            throw new RuntimeException('Deepseek API error: ' . ($decodedResponse['error']['message'] ?? 'Unknown error'));
        }

        if (!isset($decodedResponse['choices'][0]['message']['content'])) {
            throw new RuntimeException('Deepseek API error: Invalid response structure');
        }

        return trim($decodedResponse['choices'][0]['message']['content']);
    }

    private function makeRequest(array $payload): string|false
    {
        $ch = curl_init(self::API_URL);

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $this->apiKey,
            ],
            CURLOPT_TIMEOUT => 60,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);

        if ($httpCode !== 200) {
            throw new RuntimeException("Deepseek API error: HTTP $httpCode");
        }

        return $response;
    }
}