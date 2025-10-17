<?php
declare(strict_types=1);

namespace Translation\Domain\Service;

use RuntimeException;

final class OpenAITranslator extends AbstractAITranslator
{
    private OpenAI $openIA;

    public function __construct(private readonly string $apiKey)
    {
        $this->openIA = new OpenAI($this->apiKey);
    }

    protected function getEngineIdentifier(): string
    {
        return 'openai';
    }

    public function translate(string $text, string $targetLocale): string
    {
        // Use inherited buildPrompt method
        $system = $this->buildPrompt($targetLocale);

        $response = $this->openIA->chat([
            'model' => 'gpt-3.5-turbo',
            'messages' => [
                [
                    'role' => 'system',
                    'content' => $system
                ],
                [
                    'role' => 'user',
                    'content' => $text
                ]
            ],
            'temperature' => 1.5,
            'max_tokens' => 1000,
            'frequency_penalty' => 0,
            'presence_penalty' => 0,
        ]);

        if (!$response) {
            throw new RuntimeException('OpenAI API error');
        }

        $decodedResponse = json_decode($response);

        return $decodedResponse->choices[0]->message->content;
    }
}
